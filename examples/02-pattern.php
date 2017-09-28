<?php

//载入类文件
//实际使用中请通过composer安装，它有自动载入功能
require __DIR__ . '/loader.php';

//模拟请求
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/aa/bb/cc';
//$_SERVER['REQUEST_URI'] = '/optiona2/news(;¬_¬)2017(;¬_¬)09';

$router = new \Bybzmt\Router\Router();

/*
 * 回调参数格式说明
 *
 * 格式为: ":类名:方法名:映射1:映射2:...:映射N"
 * 如果不是正则匹配的那么映射是可选的
 *
 * 映射应该与路由中捕获的参数数量一至，它会将参数映射到$_GET数组中去
 *
 * 如访问链接为: /aa/bb/cc 那么下面设置会访问到
 * 类: Example 方法: test 参数: $_GET['a'] = 'bb'; $_GET['b'] = 'cc';
 * 注意！类名需要包含命名空间 如: Example\Example
 */
$router->get('/aa/(\w+)/(\w+)', ':Example:test:a:b');

//注意！ 1. 这样写是不准许的,正则必需要有"()"包起来
//注意！ 2. 正则默认是大小写敏感的！
//$router->get('/aa/\w+/\w+', ':Example:test');

//注意！ 括号数量要和key的数量一至！这样子写就不对！
//$router->get('/aa/(\w+)/(\w+)', ':Example:test:k1');

//静态路由: 首页
$router->get('/', ':Example:test');

//静态路由效率最高
$router->get('/static/route', ':Example:test');

//正则匹配 多个参数
//注意！如果有多个正则可以匹配同一个链接，先注册的规则会命中
//比如与下条规则同用，必需保证这条必需在上面！
$router->get('/regex/(\w+)/(\w+)', ':Example:test:k1:k2');

//正则匹配 单个参数
$router->get('/regex/(\w+)', ':Example:test');

//正则匹配 可选参数
//可匹配 /optional 或 /optional/2017 或 /optional/2017/09
$router->get('/optional/news(/\d+(/\d+)?)?', ':Example:test:k1:k2');

//注意！ 上面的路由实际会捕获到"/2017"、"/09" 有个前缀"/" 可以用下面方法去除
//注意！ 去除的格式为: 前缀 + 空格 + 键名
$router->get('/optional/news(/\d+(/\d+)?)?', ':Example:test:/ k1:/ k2');

//实际我们还能这样子和那样子匹配（额～还是别作。。。
$router->get('/optiona2/news(-\d+(-\d+)?)?', ':Example:test:- d1:- d2');
$router->get('/optiona2/news(→_→\d+(→_→\d+)?)?', ':Example:test:→_→ d1:→_→ d2');
$router->get('/optiona2/news(\(;¬_¬\)\d+(\(;¬_¬\)\d+)?)?', ':Example:test:(;¬_¬) d1:(;¬_¬) d2');

//注意！ 这样写是错的，不能正确匹配。 "/"必需放到括号中才行！
//$router->get('/optional/news/(\d+/(\d+)?)?', ':Example:test:k1:k2');

//正则匹配 开头就是正则
//这种相对而言效率最差，不过实际速度也很快，一般不需要太在意
$router->get('/(\w+)/suffix', ':Example:test');


//执行路由动作
$router->run();


