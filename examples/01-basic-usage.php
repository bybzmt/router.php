<?php

//载入类文件
//实际使用中请通过composer安装，它有自动载入功能
require __DIR__ . '/loader.php';

//模拟请求
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/news/this_is_key1';

$router = new \Bybzmt\Router\Router();

//静态路由: 首页
$router->get('/', ':Example:test');

//使用正则匹配
//第2个参数格式为: ":类名:方法名:映射1:映射2:...:映射N"
$router->get('/news/(\w+)', ':Example:test:key1');

//其它请求方法
$router->post('/', ':Example:test');
$router->put('/', ':Example:test');
$router->delete('/', ':Example:test');
$router->patch('/', ':Example:test');
$router->options('/', ':Example:test');

//匹配所有方法 (GET|POST|PUT|DELETE|PATCH|OPTIONS)
$router->all('/all', ':Example:test');

//注意! 没有head()方法 HEAD会自转转到GET上面
//@url http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.4

//区配任意指定的方法 多个方法以|分隔
$router->handle("GET|POST|PUT",  "/handle", ':Example:test');

//路由可以接受回调函数
//只要是合法的Callable类型的值都可以
$router->get('/', function(){
    echo "首页\n";
});

//回调函数可以有参数
//捕获到的参数会按顺序传给回调函数
//注意！参数必需都有默认值，防止因缺少参数出现代码报错！
$router->get('/callback/(\w+)/(\w+)', function($aa=null, $bb=null){
    echo "参数: $aa, $bb\n";
});

//修改默认404页
$router->set404(function(){
    echo "~404~";
});


//执行路由动作
$router->run();


