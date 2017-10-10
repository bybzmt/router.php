<?php
//载入类文件
//实际使用中请通过composer安装，它有自动载入功能
require __DIR__ . '/loader.php';

//模拟请求
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/callback/a1/b2';



/**
 * 自定义路由分发 - 修改注册的格式
 */
class MyRouter extends \Bybzmt\Router\Router
{
    //修改正则改为大小写不敏感
    protected $_regex_left = '#^';
    protected $_regex_right = '$#i';

    /**
     * 解析注册的回调
     *
     * 反回格式: [$class, $method, $keys, $map]
     *
     * $map 是路由的key名(反向路由用)
     *
     * $keys 是参数映射的key 格式为: [][$prefix, $key, $optional]
     */
    protected function parseFunc($func)
    {
        $class = 'Example';
        $method = 'test';

        return array(
            $class,
            $method,
            array(
                array('', 'key1', true),
                array('', 'key2', false),
            ),
            $class . '.'. $method
        );
    }

    /*
     * 它是默认的404页面，也能重写掉它
     */
    protected function default404()
    {
        header('HTTP/1.0 404 Not Found');
        echo "My 404 page not found\n";
    }
}

//------------------------

//一个更简单的修改方法
class MyRouter2 extends \Bybzmt\Router\Router
{

    //一个更简单的修改方法
    protected function parseFunc($func)
    {
        $data = parent::parseFunc($func);

        //可以这里修改class/method
        $data[0] = 'Bybymt\\Blog\\Web\\Controller\\' . $data[0];
        $data[1] = $data[1].'Action';

        return $data;
    }
}

//----------------------
$router = new MyRouter();

/*
 * 自定义的路由分发 MyRouter
 */
$router->get('/callback/(\w+)/(\w+)', ':example.test:k1:k2');

//执行路由动作
$router->run();
