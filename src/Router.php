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
            $uri = substr($_SERVER['REQUEST_URI'], strlen($this->getBasePath()));

            $this->_uri = parse_url($uri, PHP_URL_PATH);
        }

        return $this->_uri;
    }

    public function getBasePath()
    {
        // Check if server base path is defined, if not define it.
        if (null === $this->_basePath) {
            $this->_basePath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1));
        }

        return $this->_basePath;
    }

    public function saveCache($file)
    {
        file_put_contents($file, '<?php' . var_export($this->getRoutes(), true));
    }

    /**
     * 默认404页面
     */
    protected function default404() {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        echo "404 page not found";
        return false;
    }

    /**
     * 默认分发方法
     */
    protected function dispatch($func, array $params) {
        if (is_string($func) && strpos($func, '@') !== false) {
            list($controller, $method) = explode('@', $func);

            return call_user_func_array(array(new $controller, $method), $params);
        }

        if (is_callable($func)) {
            return call_user_func_array($func, $params);
        }

        throw new Exception("callback: $func is not callable!");
    }
}
