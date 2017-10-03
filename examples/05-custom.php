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
     * 路由分发执行
     *
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
     * 这是一种典型的修改
     *
     * 由于func的类需要有完整命名空间，所以看上去很长很累赘
     * 但是由于路由库不能确定func在特定项目什么样更好，这里
     * 提供一种自定义的func映射方的方式
     *
     * 在这里示例: 自动给class增加前缀和method增加后缀
     *
     * (函数应该叫: dispatch 这里只是为了方便作例子改了个名)
     */
    protected function dispatch2($func, array $params)
    {
        //快速判断func格式类型
        //不过一般特定项目只会使用一种形式，这个判可以忽略
        //if (is_string($func) && $func[0] == $this->_func_separator)

        $tmp = explode($this->_func_separator, $func, 4);

        //func格式规定最短为:class:method所以tmp[2]一定存在
        $tmp[1] = 'Bybymt\\Blog\\Web\\' . $tmp[1];
        $tmp[2] = $tmp[2].'Action';

        $newFunc = implode($this->_func_separator, $tmp);

        return parent::dispatch($newFunc, $params);
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


