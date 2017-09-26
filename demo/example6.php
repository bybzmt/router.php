<?php
//载入类文件
//实际使用中请通过composer安装，它有自动载入功能
require __DIR__ . '/loader.php';

//模拟请求
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/dd/aa/bb/5/10';


//如果你有很多条的规则，而且你又特别特别求一个极致效率，那么你可以这么做
//特别说明：一般不需要这么做！
$router = new \Bybzmt\Router\Router();

// ----- 这里只是演示下任意值都可以 -------
$router->get('/aa', function(){ echo "aa\n"; });
$router->get('/bb', function(){ echo "aa\n"; });
$router->get('/bb/a1', new stdClass());
$router->get('/bb/a2', null);
$router->get('/bb/a3', 1);
$router->get('/bb/a4', false);
$router->get('/dd/aa', ':example:test');
$router->get('/dd/aa/(\d{4}(/\d{2}(/\d{2})?)?)?\.php', ':example:test:/ k1:/ k2:/ k3');
$router->get('/dd/aa/(\d{4}(-\d{2}(-\d{2})?)?)?\.php', ':example:test:k1:k2:k3');
$router->get('/dd/aa/(\d+)', ':example:test:k1');
$router->get('/dd/(\d+)', ':example:test:k1');
//------- 路由注册结束 ---------

echo "------------ 生成的代码开始 ----------\n";
$routes = $router->getRoutes();

$tool = new \Bybzmt\Router\Tool($routes);
$out = $tool->exportReverse();

var_dump($out);


echo "------------ 生成的代码结速 ----------\n";
