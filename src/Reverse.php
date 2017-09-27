<?php
namespace Bybzmt\Router;

/**
 * 链接地址拼接器
 *
 * 它根据己注册的路由进行反向路由
 */
class Reverse
{
    //反向路由数据
    protected $_map;

    /**
     * 初始化反向路由
     *
     * @param map 用于载入之前缓存的反向路由规则
     */
    function __construct(array $map=[])
    {
        $this->_map = $map;
    }

    /**
     * 创建链接地址
     *
     * 目标对像: 请求方法 + 空格 + 类名:类方法
     * 一般情况下请求方法可省略
     * 仅在将同一个"类名:类方法"注册到多个不同方法中时需要
     *
     * @param action 目标对像
     * @param params 请求参数
     */
    function buildUri(string $action, array $params=[])
    {
        $method = "GET";
        $func = $action;

        if (strpos($action, " ") !== false) {
            list($method, $func) = explode(" ", $action, 2);
        }

        $func = ltrim($func, '\\ ');

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

    protected function _build(array $route, array $params, bool $throwErr)
    {
        //无参数的就是静态映射不需要验证
        if (count($route[2]) == 0) {
            return [$route[1], $route[2]];
        }

        $param_arr = [$route[1]];

        foreach ($route[2] as $tmp) {
            list($must, $prefix, $key) = $tmp;

            if (isset($params[$key])) {
                $param_arr[] = $prefix . $params[$key];
                unset($params[$key]);
            } else {
                if (!$must) {
                    $param_arr[] = "";
                } else {
                    //缺少必选参数
                    if ($throwErr) {
                        throw new Exception("生成链接 缺少参数:$key");
                    } else {
                        return null;
                    }
                }
            }
        }

        $uri = call_user_func_array('sprintf', $param_arr);

        //验证是否符合正则要求
        if (preg_match($route[0], $uri)) {
            return [$uri, $params];
        }

        if ($throwErr) {
            throw new Exception("生成的Uri:$uri 不符合正则:{$route[0]} 要求");
        } else {
            return null;
        }
    }

}
