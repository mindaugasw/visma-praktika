<?php

namespace App\Service;

use App\Exception\ObjectNotFoundException;

class Router
{
    const CONTROLLER_BASE_PATH = '/Controller';
    const DEFAULT_CONTROLLER_NAME = 'NotImplemented'; // TODO
    
    private App $app;
    
    public function __construct(App $app)
    {
        $this->app = $app;
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
        
        // find class
        [$controllerObj, $actionPath] = $this->findClass($actionPath);
        $methodName = $this->findMethod($controllerObj, $actionPath);
        
        parse_str($url['query'], $queryArgs);
        $controllerObj->$methodName($queryArgs);
    }
    
    /**
     * Iterate through a given path and find valid class name.
     * Return found class object and remaining path.
     *
     * e.g. with $actionPath = ['api', 'words']
     * the following classes will be attempted to load:
     * App\Controller\ApiController
     * App\Controller\Api\WordsController
     *
     * @param array $actionPath
     * @return array [ControllerObj, remaining $actionPath]
     */
    private function findClass(array $actionPath): array
    {
        if (empty($actionPath)) { // default controller
            $className = self::DEFAULT_CONTROLLER_NAME;
            return [
                new $className($this->app),
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
                $className = 'App'.$className;
                return [
                    new $className($this->app),
                    array_slice($actionPath, $i + 1)
                ];
            }
        }
        
        throw new ObjectNotFoundException('Controller not found');
        /*return [
            false,
            $actionPath
        ];*/
    }
    
    /**
     * Priority:
     * method defined in $actionPath, e.g. myAction_get()
     * request method name, e.g. get()
     * index method, e.g. index_get()
     *
     * @param object $controller
     * @param array $actionPath
     * @return string
     */
    private function findMethod(object $controller, array $actionPath): string
    {
        $methodNames = [];
        $requestMethod = strtolower($_SERVER['REQUEST_METHOD']);
        if (count($actionPath) === 1) {
            $methodNames[] = sprintf('%s_%s', $actionPath[0], $requestMethod); // myAction_get()
        } elseif (count($actionPath) === 0) {
            $methodNames[] = $requestMethod; // get()
            $methodNames[] = 'index_'.$requestMethod; // index_get()
        }
        
        foreach ($methodNames as $method) {
            if (method_exists($controller, $method)) {
                return $method;
            }
        }
        
        throw new ObjectNotFoundException('Controller method not found');
    }
}
