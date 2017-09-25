<?php
namespace Example;

//载入类文件
//实际使用中请通过composer安装，它有自动载入功能
require __DIR__ . '/loader.php';

//模拟请求
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/optional';

$router = new \Bybzmt\Router\Router();

/*
 * 回调参数格式说明
 *
 * 格式为: ":类名:方法名:映射1:映射2:...:映射N"
 * 其中映射是可选的
 *
 * 映射应该与路由中捕获的参数数量一至，它会将参数映射到$_GET数组中去
 *
 * 如访问连接为: /aa/bb/cc 那么下面设置会访问到
 * 类名: example 方法名: test 参数: $_GET['a'] = 'bb'; $_GET['b'] = 'cc';
 * 注意！类名需要有命名空间并以\开头
 *
 * 另外回调参数也可以设为一个回调函数
 * 可参考 example2.php
 *
 * 如果认为这种路由映射不满足需求,只要继承Router类并重写掉dispatch()方即可
 * 可参考 example3.php
 */
$router->get('/aa/(\w+)/(\w+)', ':\Example\Example:test:a:b');

//静态路由: 首页
$router->get('/', ':\Example\Example:test');

//静态路由效率最高
$router->get('/static/route', ':\Example\Example:test');


//正则匹配 多个参数
//注意！如果有多个正则可以匹配同一个链接，先注册的规则会命中
//比如与下条规则同用，必需保证这条必需在上面！
$router->get('/regex/(\w+)/(\w+)', ':\Example\Example:test');

//正则匹配 单个参数
$router->get('/regex/(\w+)', ':\Example\Example:test');

//正则匹配 可选参数
//可匹配 /optional 或 /optional/2017 或 /optional/2017/09
$router->get('/optional(/\d+(/\d+)?)?', ':\Example\Example:test');

//正则匹配 开头就是正则
//这种相对而言效率最差，不过实际速度也很快，一般不需要太在意
$router->get('/(\w+)/suffix', ':\Example\Example:test');


//-----其它请求方法------
$router->post('/', ':\Example\Example:test');
$router->put('/', ':\Example\Example:test');
$router->delete('/', ':\Example\Example:test');
$router->patch('/', ':\Example\Example:test');
$router->options('/', ':\Example\Example:test');

//注意！没有head()方法,HEAD会自动转到GET方法中，并抛弃掉返回
// If it's a HEAD request override it to being GET and prevent any output, as per HTTP Specification
// @url http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4
//$router->head('/', ':\Example\Example:test');

//匹配所有方法 (GET|POST|PUT|DELETE|PATCH|OPTIONS)
$router->all('/all', ':\Example\Example:test');

//区配任意指定的方法 多个方法以|分隔
$router->handle("GET|POST|PUT",  "/handle", ':\Example\Example:test');

//执行路由动作
$router->run();

//-------------------------
class Example
{
    public function test()
    {
        echo "example::test in " . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'];
        echo "\n";
    }
}
