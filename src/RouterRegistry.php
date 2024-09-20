<?php namespace Albreis;

use ReflectionMethod;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionFunction;

class RouterRegistry
{

    protected $files = [];

    public function __construct(
        protected Router $router
    ) {
    }

    function parseRouteAnnotation($docComment)
    {
        // Regex para capturar o padrão da rota e o método HTTP
        if (preg_match('/@Route::([\w]+)\(\'([^\']+)\'\)/', $docComment, $matches)) {
            return ['method' => $matches[1], 'pattern' => $matches[2]];
        }
        if (preg_match('/@Route::([\w]+)\(\"([^\"]+)\"\)/', $docComment, $matches)) {
            return ['method' => $matches[1], 'pattern' => $matches[2]];
        }
        return null;
    }

    function getCallback($className, $methodName)
    {
        if (class_exists($className)) {
            return "$className::$methodName";
        }
        return $methodName;
    }

    function extractRoutesFromClass($className)
    {
        $reflectionClass = new ReflectionClass($className);
        $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);
        $routes = [];

        foreach ($methods as $method) {
            $docComment = $method->getDocComment();
            if ($docComment) {
                $routeInfo = $this->parseRouteAnnotation($docComment);
                if ($routeInfo) {
                    $callback = $this->getCallback($className, $method->getName());
                    $routes[] = ['method' => $routeInfo['method'], 'pattern' => $routeInfo['pattern'], 'callback' => $callback];
                }
            }
        }

        return $routes;
    }

    function extractRoutesFromFunctions()
    {
        $functions = get_defined_functions()['user'];
        $routes = [];

        foreach ($functions as $function) {
            $reflectionFunction = new ReflectionFunction($function);
            $docComment = $reflectionFunction->getDocComment();
            if ($docComment) {
                $routeInfo = $this->parseRouteAnnotation($docComment);
                if ($routeInfo) {
                    $routes[] = ['method' => $routeInfo['method'], 'pattern' => $routeInfo['pattern'], 'callback' => $function];
                }
            }
        }

        return $routes;
    }

    function recursiveGlob($directory, $pattern)
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        $files = [];

        foreach ($iterator as $file) {
            if ($file->isFile() && preg_match($pattern, $file->getFilename())) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }

    function autoload($namespace = null)
    {
        $namespace = str_replace('/','\\', $namespace);
        // Extract routes from functions
        $functionRoutes = $this->extractRoutesFromFunctions();
        foreach ($functionRoutes as $route) {
            $this->router->{$route['method']}($route['pattern'], $route['callback']);
        }

        // Extract routes from classes
        $declaredClasses = get_declared_classes();
        foreach ($declaredClasses as $class) {
            if (strpos($class, $namespace) === 0) {
                $classRoutes = $this->extractRoutesFromClass($class);
                foreach ($classRoutes as $route) {
                    $this->router->{$route['method']}($route['pattern'], $route['callback']);
                }
            }
        }
        return $this;
    }

    function add($class)
    {
        // Extract routes from class
        $classRoutes = $this->extractRoutesFromClass($class);
        foreach ($classRoutes as $route) {
            $this->router->{$route['method']}($route['pattern'], $route['callback']);
        }
        return $this;
    }

    function loadFrom($entry, $namespace = null)
    {
        if (is_array($entry)) {
            foreach ($entry as $directory) {
                $this->loadFrom($directory);
            }
        } else {
            if (is_file($entry)) {
                    $this->loadFile($entry);
            } else {
                $phpFiles = $this->recursiveGlob($entry, '/^.*\.php$/');
                foreach ($phpFiles as $file) {
                        $this->loadFile($file);
                }
            }
        }
        $this->autoload($namespace);
        return $this;
    }

    protected function loadFile($file)
    {
        if (isset($this->files[$file])) {
            return $this;
        }
        require_once $file;
        $this->files[$file] = true;
        return $this;
    }
}
