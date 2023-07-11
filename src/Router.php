<?php namespace Albreis;

use ReflectionMethod;
use Closure;
use ReflectionException;
use ReflectionFunction;
use ReflectionParameter;

class Router
{
    /**
     * @var array
     */
    private $routes = [];

    public $output;
    public $prefix;

    public $before_callback = null;

    public $after_callback = null;

    public $allowed_methods = [
        'GET',
        'POST',
        'PATCH',
        'DELETE',
        'PUT',
        'OPTIONS',
        'HEAD',
    ];

    public $method;

    public $uri;

    public function __construct($method = null, $uri = null)
    {
        $this->setMethod($method);
        $this->uri = $uri;
        $this->uri();
    }

    /**
     * @return string
     * Teste para retorna o método da requisição atual
     * @test return (new Albreis\Router)->method()
     * @expect return "cli"
     * Teste simulando uma requisição GET
     * @test return (new Albreis\Router('GET'))->method()
     * @expect return "GET"
     * Teste simulando um verbo não existente
     * @test return new Albreis\Router('FOO')
     * @expect return "Method not allowed"
     * Teste de classe declarada de forma incorreta
     * @test return new Router;
     * @expect return 'Class "Router" not found'
     */
    public function method()
    {

        if ($this->method) {
            return strtolower($this->method);
        }

        return isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : 'cli';
    }

    /**
     * @test $router = new Albreis\Router; $router->setMethod('POST'); return $router->method;
     * @expect return 'POST'
     *
     * @test $router = new Albreis\Router; $router->setMethod('BAR'); return $router->method;
     * @expect return 'Method not allowed'
     */

    public function setMethod($method)
    {
        
        if (!$method) {
            return;
        }

        if (!in_array($method, $this->allowed_methods)) {
            throw new Exception('Method not allowed');
        }

        $this->method = $method;
    }

    /**
     * @return string
     *
     * @test return (new Albreis\Router)->uri()
     * @expect return 'src'
     *
     * @test return (new Albreis\Router(null, '/homepage'))->uri()
     * @expect return '/homepage'
     */
    public function uri()
    {

        if ($this->uri) {
            return $this->uri;
        }

        if ($this->method() == 'cli') {
            global $argv;

            if (!isset($argv[1])) {
                throw new Exception("URI is required", 1);
            }

            $arguments = $argv;

            unset($arguments[0]);

            if (isset($argv[2])) {
                $_SERVER['REQUEST_METHOD'] = array_shift($arguments);
                $_SERVER['REQUEST_URI'] = array_shift($arguments);
            } else {
                $_SERVER['REQUEST_URI'] = array_shift($arguments);
            }

            $url = parse_url($_SERVER['REQUEST_URI']);

            if (count($argv) > 3) {
                parse_str(implode('&', $arguments), $_POST);
            }

            if (isset($url['query'])) {
                parse_str($url['query'], $_GET);
            }

            $_REQUEST = $_GET + $_POST;
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

    public function prefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * is triggered when invoking inaccessible methods in an object context.
     *
     * @param $name string
     * @param $arguments array
     * @return mixed
     * @link http://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.methods
     *
     */
    public function __call($name, $arguments)
    {
        list($path, $callback, $bypass) = array_pad($arguments, 3, '');

        if (is_callable($path)) {
            $callback = $path;
            $path = '.*';
        }

        return $this->exec($name, $path, $callback, $bypass);
    }

    public function before($callback = null)
    {
        $this->before_callback = $callback;
        return $this;
    }

    public function after($callback = null)
    {
        $this->after_callback = $callback;
        return $this;
    }

    /**
     * @param $method
     * @param $uri
     * @return mixed|null
     */
    private function exec($method, $path, $callback, $bypass = false, $ret = false)
    {
        if (is_array($method)) {
            foreach ($method as $m) {
                $this->exec($m, $path, $callback, $bypass);
            }
            return;
        }
        $method = strtolower($method);
        $uri = $this->prefix.$path;
        $pattern = str_replace('/', '\/', $uri);
        $route = '/' . $pattern . '/';
        if (($method == $this->method() || $method == '*' || $method == 'all' || $method == 'any') && preg_match($route, $this->uri(), $parameters)) {
            array_shift($parameters);
            if ($this->before_callback) {
                $this->call($this->before_callback, $parameters);
            }
            $this->output = $this->call($callback, $parameters);
            if ($this->after_callback) {
                $this->call($this->after_callback, $parameters);
            }
            $this->before_callback = null;
            $this->after_callback = null;
            if (!$bypass && !$ret) {
                exit;
            }
            if ($ret) {
                return $this->output;
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
        if (is_string($callback) && count($call = explode('::', $callback)) == 2) {
            $method = new ReflectionMethod($call[0], $call[1]);
            if (!$method->isStatic()) {
                $callback = [new $call[0], $call[1]];
            }
        }
        if (is_callable($callback)) {
            if ($callback instanceof Closure == true || is_string($callback)) {
                $method = new ReflectionFunction($callback);
            }
            $params = $method->getParameters();
            foreach ($params as $parameter) {
                try {
                    $param = new ReflectionParameter($callback, $parameter->getName());
                    if ($param->getClass()) {
                        $class = $param->getClass()->getName();
                        $parameters[$parameter->getName()] = new $class;
                    }
                } catch (ReflectionException $e) {
                }
            }
            try {
                new ReflectionParameter($callback, 'router');
                $parameters['router'] = $this;
            } catch (ReflectionException $e) {
            }
            $return = call_user_func_array($callback, $parameters);
            return $return;
        }
    }
}
