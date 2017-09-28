<?php
//载入类文件
//实际使用中请通过composer安装，它有自动载入功能
require __DIR__ . '/loader.php';

//路由核心 它只有匹配功能
$router = new \Bybzmt\Router\Basic();

//注册一个匹配函数
$router->handle("GET|POST", '/callback/(\w+)/(\w+)', 'func');

//返回注册的路由规则方便调试
$routes = $router->getRoutes();

//进行路由匹配
if (list($func, $params) = $router->match('GET', '/callback/a1/b2')) {
    //就是之前注册的原样返回
    var_dump($func);
    //就是捕获到的数组 ['a1', 'b2']
    var_dump($params);
} else {
    echo "未命中，可以显示404了\n";
}
