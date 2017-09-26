<?php
namespace Bybzmt\Router;

class Router extends Basic {
    private $_basePath;
    private $_uri;
    private $_method;
    private $_notFoundFunc;

    public function set404($func) {
        $this->_notFoundFunc = $func;
        return $this;
    }

    public function setBasePath(string $base) {
        $this->_basePath = $base;
        return $this;
    }

    public function setUri(string $uri) {
        $this->_uri = $uri;
        return $this;
    }

    public function setMethod(string $method)
    {
        $this->_method = $method;
        return $this;
    }

    public function run()
    {
        $method = $this->getMethod();
        $uri = $this->getUri();

        // If it's a HEAD request override it to being GET and prevent any output, as per HTTP Specification
        // @url http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
        $ob_start = false;
        if ($method === 'HEAD') {
            ob_start(function(){}, 4096);
            $method = "GET";
            $ob_start = true;
        }

        if (list($func, $params) = $this->match($method, $uri)) {
            $out = $this->dispatch($func, $params);
        } else if ($this->_notFoundFunc) {
            $out = $this->dispatch($this->_notFoundFunc, []);
        } else {
            $out = $this->default404();
        }

        if ($ob_start) {
            ob_end_clean();
        }

        return $out;
    }

    public function all(string $pattern, $func)
    {
        $this->handle("GET|POST|PUT|PATCH|DELETE|OPTIONS", $pattern, $func);
        return $this;
    }

    public function get(string $pattern, $func)
    {
        $this->handle("GET", $pattern, $func);
        return $this;
    }

    public function post(string $pattern, $func)
    {
        $this->handle("POST", $pattern, $func);
        return $this;
    }

    public function put(string $pattern, $func)
    {
        $this->handle("PUT", $pattern, $func);
        return $this;
    }

    public function patch(string $pattern, $func)
    {
        $this->handle("PATCH", $pattern, $func);
        return $this;
    }

    public function delete(string $pattern, $func)
    {
        $this->handle("DELETE", $pattern, $func);
        return $this;
    }

    public function options(string $pattern, $func)
    {
        $this->handle("OPTIONS", $pattern, $func);
        return $this;
    }

    public function getMethod()
    {
        if ($this->_method === null) {
            if (!isset($_SERVER['REQUEST_METHOD'])) {
                $_SERVER['REQUEST_METHOD'] = 'GET';
            }

            // Take the method as found in $_SERVER
            $method = $_SERVER['REQUEST_METHOD'];

            if ($_SERVER['REQUEST_METHOD'] == 'POST' &&
                isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']) &&
                in_array($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'], array('PUT', 'DELETE', 'PATCH'))
            ) {
                $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
            }

            $this->_method = $method;
        }

        return $this->_method;
    }

    public function getUri()
    {
        if ($this->_uri === null) {
            if (!isset($_SERVER['REQUEST_URI'])) {
                $_SERVER['REQUEST_URI'] = '/';
            }

            $uri = substr($_SERVER['REQUEST_URI'], strlen($this->getBasePath()));

            $this->_uri = parse_url($uri, PHP_URL_PATH);
        }

        return $this->_uri;
    }

    public function getBasePath()
    {
        // Check if server base path is defined, if not define it.
        if (null === $this->_basePath) {
            if (!isset($_SERVER['SCRIPT_NAME'])) {
                $_SERVER['SCRIPT_NAME'] = '/';
            }

            $this->_basePath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1));
        }

        return $this->_basePath;
    }

    /**
     * 默认404页面
     */
    protected function default404()
    {
        if (!isset($_SERVER['SERVER_PROTOCOL'])) {
            $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
        }

        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        echo "404 page not found";
        return false;
    }

    /**
     * 默认分发方法
     */
    protected function dispatch($func, array $params) {
        if (is_string($func) && $func[0] === ':') {
            $keys = explode(':', $func);

            if (count($keys) < 3) {
                throw new Exception("Dispatch '$func' Format Error");
            }

            list(, $class, $method) = $keys;

            $class = '\\'.ltrim($class, '\\');

            foreach ($params as $i => $param) {
                if (isset($keys[$i+3])) {
                    $key = $keys[$i+3];

                    //key可以为"prefix keyname"的形式
                    //这里要把prefix给去除掉
                    if (strpos($key, ' ') !== false) {
                        list($prefix, $key) = explode(' ', $key, 2);
                        $_GET[$key] = substr($param, strlen($prefix));
                    } else {
                        $_GET[$key] = $param;
                    }
                }
            }

            if (!class_exists($class)) {
                throw new Exception("Dispatch '$func' Class Not Exists");
            }

            $obj = new $class();

            if (!method_exists($obj, $method)) {
                throw new Exception("Dispatch '$func' Method Not Exists");
            }

            return call_user_func_array(array($obj, $method), $params);
        }

        if (is_callable($func)) {
            return call_user_func_array($func, $params);
        }

        throw new Exception("Dispatch Callback Is Not Callable!");
    }
}
