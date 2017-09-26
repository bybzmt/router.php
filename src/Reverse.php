<?php
namespace Bybzmt\Router;

/**
 * 链接地址拼接器
 *
 * 它根据己注册的路由进行反向路由
 */
class Reverse
{
    protected $_map;

    function __construct(array $map=[]) {
        $this->_map = $map;
    }

    /**
     * 创建链接地址
     *
     * @param action 命名空间\函数:方法
     */
    function buildUri(string $action, array $params=[])
    {
        $method = "GET";
        $func = $action;

        if (strpos($action, " ") !== false) {
            list($method, $func) = explode(" ", $action, 2);
        }

        $func = '\\'.ltrim($func, '\\');

        if (!isset($this->_map[$func])) {
            throw new Exception("mkUrl 映射关系:$action 未定义");
        }

        //找到对应方法的映射
        $methods = $this->_map[$func];
        if (count($methods) > 1) {
            if (!isset($methods[$method])) {
                throw new Exception("mkUrl 映射关系:$action 方法:$method 未定义");
            }

            $routes = $methods[$method];
        } else {
            $routes = current($methods);
        }

        $must = count($routes) == 1;

        foreach ($routes as $route) {
            if (list($uri, $params_new) = $this->_build($route, $params, $must)) {
                if ($params_new) {
                    $uri .= '?' . http_build_query($params_new);
                }
                return $uri;
            }
        }

        throw new Exception("mkUrl 映射:$action 与所有规则都不匹配");
    }

    protected function _build(array $route, array $params, bool $must)
    {
        $verification = '#^'.$route[0].'$#';
        $format = $route[1];
        $keys = $route[2];

        if (count($keys) == 0) {
            return [$route[0], $params];
        }

        $param_arr = [$format];

        foreach ($keys as $key) {
            $prefix = '';
            $optional  = false;

            //可选参数
            if ($key[0] == ':') {
                $optional  = true;
                list(,$prefix, $key) = explode(':', $key, 3);
            }

            if (isset($params[$key])) {
                $param_arr[] = $params[$key];
                unset($params[$key]);
            } else {
                if ($optional) {
                    $param_arr[] = "";
                } else {
                    //缺少必选参数
                    if ($must) {
                        throw new Exception("生成链接 缺少参数:$key");
                    } else {
                        return null;
                    }
                }
            }
        }

        $uri = call_user_func_array('sprintf', $param_arr);

        //验证是否符合正则要求
        if (preg_match($verification, $uri)) {
            return [$uri, $params];
        }

        if ($must) {
            throw new Exception("生成的Uri:$uri 不符合正则:$verification 要求");
        } else {
            return null;
        }
    }

}
