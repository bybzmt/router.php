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
$router->get('/dd/aa', ':example:test');
$router->get('/dd/aa(/\d{4}(/\d{2}(/\d{2})?)?)?\.php', ':example:test:/ k1:/ k2:/ k3');
$router->get('/dd/aa(/\d{4}(-\d{2}(-\d{2})?)?)?\.php', ':example:test:k1:k2:k3');
$router->get('/dd/aa/(\d+)', ':example:test:k1');
$router->get('/dd/(\d+)', ':example:test:k1');
//------- 路由注册结束 ---------


$tool = new \Bybzmt\Router\Tool($router->getRoutes());
$code = $tool->exportReverse();

$tmpfile = tempnam(sys_get_temp_dir(), 'test_');
file_put_contents($tmpfile, $code);

echo "------------ 生成的代码开始 ----------\n";
echo $code;
echo "------------ 生成的代码结速 ----------\n";

#----- 正式程序将这么写 ----------
//直接读取之前保存的数据
$reverse = new \Bybzmt\Router\Reverse(require $tmpfile);

var_dump($reverse->buildUri('example:test', []));
var_dump($reverse->buildUri('example:test', ['k1'=>'2008']));
var_dump($reverse->buildUri('example:test', ['k1'=>'2008', 'k2'=>'09']));
var_dump($reverse->buildUri('example:test', ['k1'=>'2008', 'k2'=>'09', 'k3'=>'31']));

var_dump($reverse->buildUri('example:test', ['k1'=>'20080931']));
#----- 正式程序将就这么多 ----------

//清理 可忽略
@unlink($tmpfile);
