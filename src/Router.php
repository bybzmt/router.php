<?php
namespace Bybzmt\Router;

/**
 * 路由器
 */
class Router extends Basic
{

    //回调函数分隔符
    protected $_func_separator = ':';

    //key映射前缀分隔符
    protected $_key_prefix_separator = ' ';

    //请求方法 如:GET
    private $_method;

    //请求base路径
    private $_basePath;

    //请求地址
    private $_uri;

    //404错误回调
    private $_notFoundFunc;

    /**
     * 设置404错误回调
     */
    public function set404($func)
    {
        $this->_notFoundFunc = $func;
        return $this;
    }

    /**
     * 设置很求方法 如: GET
     */
    public function setMethod(string $method)
    {
        $this->_method = $method;
        return $this;
    }

    /**
     * 设置基础路径 (忽略请求前缀用)
     */
    public function setBasePath(string $base)
    {
        $this->_basePath = $base;
        return $this;
    }

    /**
     * 设置请求路径
     */
    public function setUri(string $uri) {
        $this->_uri = $uri;
        return $this;
    }

    /**
     * 运行路由
     */
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

    /**
     * 路由注册 充行所有方法访问
     */
    public function all(string $pattern, $func)
    {
        $this->handle("GET|POST|PUT|PATCH|DELETE|OPTIONS", $pattern, $func);
        return $this;
    }

    /**
     * 路由注册 充许GET方法访问
     */
    public function get(string $pattern, $func)
    {
        $this->handle("GET", $pattern, $func);
        return $this;
    }

    /**
     * 路由注册 充许POST方法访问
     */
    public function post(string $pattern, $func)
    {
        $this->handle("POST", $pattern, $func);
        return $this;
    }

    /**
     * 路由注册 充许PUT方法访问
     */
    public function put(string $pattern, $func)
    {
        $this->handle("PUT", $pattern, $func);
        return $this;
    }

    /**
     * 路由注册 充许PATCH方法访问
     */
    public function patch(string $pattern, $func)
    {
        $this->handle("PATCH", $pattern, $func);
        return $this;
    }

    /**
     * 路由注册 充许DELETE方法访问
     */
    public function delete(string $pattern, $func)
    {
        $this->handle("DELETE", $pattern, $func);
        return $this;
    }

    /**
     * 路由注册 充许OPTIONS方法访问
     */
    public function options(string $pattern, $func)
    {
        $this->handle("OPTIONS", $pattern, $func);
        return $this;
    }

    /**
     * 得到当前请求方法
     */
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

    /**
     * 得到当前请求路径
     */
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

    /**
     * 得到当前基础路径
     */
    public function getBasePath()
    {
        return $this->_basePath;
    }

    /**
     * 默认404页面
     */
    protected function default404()
    {
        if (PHP_SAPI != 'cli') {
            header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
        }

        echo "404 Page Not Found.";
        return false;
    }

    /**
     * 默认分发方法
     */
    protected function dispatch($func, array $params)
    {
        if (is_string($func) && $func[0] === $this->_func_separator) {
            $keys = explode($this->_func_separator, $func);

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
                    if (strpos($key, $this->_key_prefix_separator) !== false) {
                        list($prefix, $key) = explode($this->_key_prefix_separator, $key, 2);
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

            return $obj->$method();
        }

        if (is_callable($func)) {
            return call_user_func_array($func, $params);
        }

        throw new Exception("Dispatch Callback Is Not Callable!");
    }
}
