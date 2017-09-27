<?php
//载入类文件
//实际使用中请通过composer安装，它有自动载入功能
require __DIR__ . '/loader.php';

//模拟请求
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/callback/a1/b2';

/**
 * 自定义路由分发
 */
class MyRouter extends \Bybzmt\Router\Router
{
    //修改正则改为大小写不敏感  (记得这边改完反向路由那边也得改)
    protected $_regex_left = '#^';
    protected $_regex_right = '$#i';

    /**
     * @param func 之注册路由时设置的，它可以是任意值，并且会原样传到这个参数上
     * @param params 是路由捕获到的参数，没有时为空数组
     */
    protected function dispatch($func, array $params)
    {
        //可以取到原始数据
        $method = $this->getMethod();
        $baseuri = $this->getBasePath();
        $uri = $this->getUri();

        echo "这里就是示例一下，可以任意修改～\n";
        var_dump($func, $params);
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

$router = new MyRouter();

/*
 * 自定义的路由分发 MyRouter
 */
$router->get('/callback/(\w+)/(\w+)', ':example:test:k1:k2');

//执行路由动作
$router->run();


