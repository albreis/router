<?php namespace Albreis;

use ReflectionMethod;
use Exception;  

/**
 * Class Router
 * @package Albreis
 */
class Router
{
    /**
     * @var array
     */
    private $routes = [];

    public $uri;

    public function __construct($uri = null)
    {
        $this->uri = $uri;
    }

    /**
     * @return string
     */
    public function method()
    {
        return isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';
    }

    /**
     * @return string
     */
    public function uri()
    {

        if($this->uri) 
        {
            return $this->uri;
        }

        if($this->method() == 'cli') 
        {
            global $argv;

            if(!isset($argv[1])) {
                throw new Exception("URI is required", 1);
                
            }
            return $argv[1];
        }

        $self = isset($_SERVER['PHP_SELF']) ? str_replace('index.php/', '', $_SERVER['PHP_SELF']) : '';
        $uri = isset($_SERVER['REQUEST_URI']) ? explode('?', $_SERVER['REQUEST_URI'])[0] : '';
        $uri = str_replace($_SERVER['SCRIPT_NAME'], '/', $uri);
        $uri = urldecode($uri);

        if ($self !== $uri) {
            $peaces = explode('/', $self);
            array_pop($peaces);
            $start = implode('/', $peaces);
            $search = '/' . preg_quote($start, '/') . '/';
            $uri = preg_replace($search, '', $uri, 1);
        }

        return $uri;
    }

    /**
     * is triggered when invoking inaccessible methods in an object context.
     *
     * @param $name string
     * @param $arguments array
     * @return mixed
     * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.methods
     */
    function __call($name, $arguments)
    {
        list($path, $callback, $bypass) = array_pad($arguments, 3, '');
        return $this->exec($name, $path, $callback, $bypass);
    }

    /**
     * @param $method
     * @param $uri
     * @return mixed|null
     */
    private function exec($method, $path, $callback, $bypass = false)
    {
        if(is_array($method)) {
            foreach($method as $m) {
                $this->exec($m, $path, $callback, $bypass);
            }
            return;
        }
        $method = strtolower($method);
        $uri = $path;
        $pattern = str_replace('/', '\/', $uri);
        $route = '/' . $pattern . '/';
        
        if (($method == $this->method() || $method == '*' || $method == 'all') && preg_match($route, $this->uri(), $parameters)) {
            array_shift($parameters);
            $this->call($callback, $parameters);
            if(!$bypass) {
                exit;
            }
        }
    }

    /**
     * @param $callback
     * @param $parameters
     * @return mixed
     */
    private function call($callback, $parameters)
    {
        if(is_string($callback) && count($call = explode('::', $callback)) == 2) {
            $method = new ReflectionMethod($call[0], $call[1]);
            if (!$method->isStatic()) {
                $callback = [new $call[0], $call[1]];
            }
        }
        if (is_callable($callback)) {
            return call_user_func_array($callback, $parameters);
        }
        return null;
    }

}