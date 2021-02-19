<?php

namespace App\Service;

use App\Exception\HttpResponseExceptionInterface;
use App\Exception\ObjectNotFoundException;
use App\Exception\ServerErrorException;
use App\Service\DIContainer\Container;
use App\Service\Response\JsonErrorResponse;
use App\Service\Response\Response;
use App\Service\Response\ResponseHandler;

class Router
{
    private const CONTROLLER_BASE_PATH = '/Controller';
    private const DEFAULT_CONTROLLER_NAME = 'NotImplemented'; // TODO
    
    public function __construct(
        private Container $diContainer,
        private ResponseHandler $responseHandler
    ) {
    }
    
    /**
     * 1. Tries to find controller from url path or uses default
     * 2. Tries to find method from:
     * 2.1. remaining url path
     * 2.2. request method
     * 2.3. index() method
     *
     * Supported routing examples:
     * GET  /api/words/ => App\Controller\Api\WordsController->get()
     * GET  /api/words/ => App\Controller\Api\WordsController->index_get()
     * POST /main/?withArgs => App\Controller\MainController->post($argsArray)
     * POST /main/myAction => App\Controller\MainController->myAction_post()
     *
     * @package App\Service
     */
    public function route(): void
    {
        $url = parse_url($_SERVER['REQUEST_URI']); // contains url['path'], url['query']
        $actionPath = explode('/', $url['path']);
        $actionPath = array_filter(  // remove empty elements
            $actionPath,
            function ($pathElement) {
                return !empty($pathElement);
            }
        );
        
        try {
            // find class
            [$controllerObj, $actionPath] = $this->getController($actionPath);
            $methodName = $this->getMethod($controllerObj, $actionPath);
    
            parse_str($url['query'], $queryArgs);
            
            // call action
            $response = $controllerObj->$methodName($queryArgs);
            if (!is_a($response, Response::class)) {
                throw new ServerErrorException('Controller must return Response object');
            }
        } catch (HttpResponseExceptionInterface $exception) {
            $response = new JsonErrorResponse(
                $exception->getMessage(),
                $exception->getStatus()
            );
        }
        
        $this->responseHandler->returnResponse($response);
    }
    
    /**
     * Iterate through a given path and find valid controller class name.
     * Return instance of found class and remaining path.
     *
     * e.g. with $actionPath = ['api', 'words']
     * the following classes will be attempted to load:
     * App\Controller\ApiController
     * App\Controller\Api\WordsController
     *
     * @param  array $actionPath
     * @return array [ControllerObj, remaining $actionPath]
     */
    private function getController(array $actionPath): array
    {
        if (empty($actionPath)) { // default controller
            $className = self::DEFAULT_CONTROLLER_NAME;
            return [
                $this->diContainer->get($className),
                $actionPath
            ];
        }
        
        $actionPathUcfirst = array_map( // convert to ucfirst
            function ($pathElement) {
                return ucfirst($pathElement);
            },
            $actionPath
        );
        
        // find class from lowest number of path elements
        for ($i = 0; $i < count($actionPath); $i++) {
            $className = sprintf( // build class name as file path
                '%s/%sController',
                self::CONTROLLER_BASE_PATH,
                implode('/', array_slice($actionPathUcfirst, 0, $i + 1))
            );
            
            $filePath = sprintf('%s/..%s.php', __DIR__, $className);
            
            if (file_exists($filePath)) {
                $className = str_replace('/', '\\', $className); // convert file path to namespace
                $className = 'App' . $className;
                
                return [
                    //new $className($this->app),
                    $this->diContainer->get($className),
                    array_slice($actionPath, $i + 1)
                ];
            }
        }
        
        throw new ObjectNotFoundException('Controller not found');
    }
    
    /**
     * Priority:
     * method defined in $actionPath, e.g. myAction_get()
     * request method name, e.g. get()
     * index method, e.g. index_get()
     *
     * @param  object $controller
     * @param  array  $actionPath
     * @return string
     */
    private function getMethod(object $controller, array $actionPath): string
    {
        $methodNames = [];
        $requestMethod = strtolower($_SERVER['REQUEST_METHOD']);
        if (count($actionPath) === 1) {
            $methodNames[] = sprintf('%s_%s', $actionPath[0], $requestMethod); // myAction_get()
        } elseif (count($actionPath) === 0) {
            $methodNames[] = $requestMethod; // get()
            $methodNames[] = 'index_' . $requestMethod; // index_get()
        }
        
        foreach ($methodNames as $method) {
            if (method_exists($controller, $method)) {
                return $method;
            }
        }
        
        throw new ObjectNotFoundException('Action not found');
    }
}
