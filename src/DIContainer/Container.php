<?php

namespace App\DIContainer;

use App\DIContainer\Exception\ContainerException;
use App\DIContainer\Exception\ParameterNotFoundException;
use App\DIContainer\Exception\ServiceNotFoundException;
use App\DIContainer\Reference\ParameterReference;
use App\DIContainer\Reference\ServiceReference;
use Interop\Container\ContainerInterface as InteropContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Container implements InteropContainerInterface
{
    private array $services;
    private array $parameters;
    private array $serviceStore;
    
    
    public function __construct(/*array $services = [], array $parameters = []*/)
    {
        //$this->services = $services;
        //$this->parameters = $parameters;
        
        $this->services = require 'servicesConfig.php';
        $this->parameters = [];
        
        $this->serviceStore = [];
        
    }
    
    
    public function get($id): object
    {
        if (!$this->has($id)) {
            throw new ServiceNotFoundException(sprintf('Service "%s" not found', $id));
        }
        
        if (!isset($this->serviceStore[$id])) {
            $this->serviceStore[$id] = $this->createService($id);
        }
        
        return $this->serviceStore[$id];
    }
    
    public function has($id): bool
    {
        return isset($this->services[$id]);
    }
    
    public function getParameter(string $name): mixed
    {
        $tokens = explode('.', $name);
        $context = $this->parameters;
        
        while (($token = array_shift($tokens)) !== null) {
            if (!isset($context[$token])) {
                throw new ParameterNotFoundException(sprintf('Parameter "%s" not found', $name));
            }
            
            $context = $context[$token];
        }
        
        return $context;
    }
    
    
    private function createService(string $id): object
    {
        $entry = &$this->services[$id];
        
        if (!is_array($entry) || !isset($entry['class'])) {
            throw new ContainerException(sprintf('%s service entry must be an array containing "class" key', $id));
        } elseif (!class_exists($entry['class'])) {
            throw new ContainerException(sprintf('%s service class does not exist: %s', $id, $entry['class']));
        } elseif (isset($entry['lock'])) {
            throw new ContainerException(sprintf('%s service contains circular reference', $id));
        }
        
        $entry['lock'] = true;
        
        $arguments = isset($entry['arguments']) ? $this->resolveArguments($id, $entry['arguments']): [];
        
        $reflector = new \ReflectionClass($entry['class']);
        $service = $reflector->newInstanceArgs($arguments);
        
        if (isset($entry['calls'])) {
            $this->initializeService($service, $id, $entry['calls']);
        }
        
        return $service;
    }
    
    private function resolveArguments(string $name, array $argumentDefinitions): array
    {
        $arguments = [];
    
        foreach ($argumentDefinitions as $argumentDefinition) {
            if ($argumentDefinition instanceof ServiceReference) {
                $argumentServiceName = $argumentDefinition->getName();
                
                $arguments[] = $this->get($argumentServiceName);
            } elseif ($argumentDefinition instanceof ParameterReference) {
                $argumentParameterName = $argumentDefinition->getName();
                
                $arguments[] = $this->getParameter($argumentParameterName);
            } else {
                $arguments[] = $argumentDefinition;
            }
        }
        
        return $arguments;
    }
    
    private function initializeService($service, string $id, array $callDefinitions)
    {
        foreach ($callDefinitions as $callDefinition) {
            if (!is_array($callDefinition) || !isset($callDefinition['method'])) {
                throw new ContainerException(sprintf('%s service calls must be arrays containing "method" key', $id));
            } elseif (!is_callable([$service, $callDefinition['method']])) {
                throw new ContainerException(sprintf('%s service asks for call to uncallable method "%s"', $id, $callDefinition['method']));
            }
            
            $arguments = isset($callDefinition['arguments']) ? $this->resolveArguments($id, $callDefinition['arguments']) : [];
            
            call_user_func_array([$service, $callDefinition['method']], $arguments);
        }
    }
}