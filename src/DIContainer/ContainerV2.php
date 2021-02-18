<?php declare(strict_types=1);


namespace App\DIContainer;


use Psr\Container\ContainerInterface;
use ReflectionParameter;

class ContainerV2 implements ContainerInterface
{
    private static ContainerV2 $instance; // TODO remove
    
    private array $services;
    
    public function __construct()
    {
        if (isset(self::$instance)) {
            throw new \Exception();
        } else {
            self::$instance = $this;
        }
    
        $this->services = [];
    }
    
    /**
     * Get instance of $id service
     * @param string $id Full class name, including namespace, e.g. App\Service\Router
     * @return object
     */
    public function get($id): object
    {
        if (!$this->has($id)) {
            $this->services[$id] = $this->createService($id);
        }
    
        return $this->services[$id];
    }
    
    public static function getStatic(string $id): object
    {
        if (!isset(self::$instance)) {
            throw new \Exception();
        }
        
        return self::$instance->get($id);
    }
    
    /**
     * Is service with given $id already instantiated?
     * @param string $id
     * @return bool
     */
    public function has($id): bool
    {
        return isset($this->services[$id]);
    }
    
    /**
     * Create new instance of service with given $id
     * @param string $id 
     * @return object New instance of service
     */
    private function createService(string $id): object
    {
        $class = new \ReflectionClass($id);
        $params = $class->getConstructor()->getParameters();
        
        $paramServices = array_map(function (ReflectionParameter $param) {
            return $this->get($param->getType()->getName());
        }, $params);
        
        return $class->newInstanceArgs($paramServices);
    }
}