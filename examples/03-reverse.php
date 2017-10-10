<?php
//载入类文件
//实际使用中请通过composer安装，它有自动载入功能
require __DIR__ . '/loader.php';

##############################
##  这里是反向路由示例

//模拟请求
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/a1/5/10';

//1. 先注册几个路由
$router = new \Bybzmt\Router\Router();
$router->get('/a1', ':example.test');
$router->get('/a2/(\d+)', ':example.test:k1');
$router->get('/a3/(\d+)/(\d+)', ':example.test:k1:k2');

//2. 将注册的路由数据转换成反向路由需要的数据
$tool = new \Bybzmt\Router\Tool($router->getRoutes());
$data = $tool->convertReverse();

var_dump($data);

//注意！回调形式的是无法自动转换的
//如果一定要转换你可以手动修改$data数据
//自动转换是根据正则分析出来的，
//如果有特殊正则解析不正确可以手动改一下$data数据

//3. 将数据放过来就行了
$reverse = new \Bybzmt\Router\Reverse($data);

//这会得到 /a1
var_dump($reverse->buildUri('example.test'));
//这会得到 /a2/2008
var_dump($reverse->buildUri('example.test', array('k1'=>'2008')));
//这会得到 /a3/2008/09
var_dump($reverse->buildUri('example.test', array('k1'=>'2008', 'k2'=>'09')));
//这会得到 /a3/2008/09?k3=31
var_dump($reverse->buildUri('example.test', array('k1'=>'2008', 'k2'=>'09', 'k3'=>'31')));
//这会得到/a1?k1=word
var_dump($reverse->buildUri('example.test', array('k1'=>'word')));

