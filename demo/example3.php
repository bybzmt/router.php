<?php
//载入类文件
//实际使用中请通过composer安装，它有自动载入功能
require __DIR__ . '/loader.php';

//模拟请求
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/callback/a1/b2';

/**
 * 自定义路由分发
 *
 * 如果你觉得这样还不行那可以看一下 example4.php
 */
class MyRouter extends \Bybzmt\Router\Router
{
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

        if ($func == '/') {
            $obj = new Example();
            return call_user_func_array($obj, 'test');
        } else {
            echo "这里就是示例一下，可以任意修改～\n";
        }
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

//-------------------------
class Example
{
    public function test()
    {
        echo "example::test in " . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'];
        echo "\n";
        var_dump($_GET);
        echo "\n";
    }
}


