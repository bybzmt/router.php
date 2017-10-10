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
    function __construct(array $map=array())
    {
        $this->_map = $map;
    }

    /**
     * 创建链接地址
     *
     * 注意! 当有一个func映射到多个路径上时以第1个符合匹配的为准
     *
     * @param func 目标对像 格式: "类名:类方法"
     * @param params 请求参数
     */
    function buildUri(string $func, array $params=array())
    {
        if (!isset($this->_map[$func])) {
            throw new Exception("mkUrl 映射关系:$func 未定义");
        }

        $routes = $this->_map[$func];
        $onlyOne = count($routes) == 1;

        //循环所有规则直到有一个符合的为止
        foreach ($routes as $route) {
            if (list($uri, $params_new) = $this->_build($route, $params, $onlyOne)) {
                if ($params_new) {
                    $uri .= '?' . http_build_query($params_new);
                }
                return $uri;
            }
        }

        throw new Exception("mkUrl 映射:$func 与所有规则都不匹配");
    }

    protected function _build(array $route, array $params, bool $onlyOne)
    {
        //静态映射不需要验证
        if ($route[0] == null) {
            return array($route[1], $params);
        }

        $param_arr = array($route[1]);

        foreach ($route[2] as $tmp) {
            list($prefix, $key, $optional) = $tmp;

            if (isset($params[$key])) {
                $param_arr[] = $prefix . $params[$key];
                unset($params[$key]);
            } else {
                if ($optional) {
                    $param_arr[] = "";
                } else {
                    //当只一条时 严格验证参数
                    if ($onlyOne) {
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
            return array($uri, $params);
        }

        return null;
    }

}
