<?php
//载入类文件
//实际使用中请通过composer安装，它有自动载入功能
require __DIR__ . '/loader.php';

//模拟请求
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/dd/aa/bb/5/10';

//如果你有很多条的规则，而且你又特别想要一个极致效率，那么你可以这么做
$router = new \Bybzmt\Router\Router();

// ----- 这里只是演示下-------
$router->get('/aa', function(){ echo "aa\n"; });
$router->get('/bb', function(){ echo "aa\n"; });
$router->get('/dd/aa', ':example:test');
$router->get('/dd/aa(/\d{4}(/\d{2}(/\d{2})?)?)?', ':example:test:/ k1:/ k2:/ k3');
$router->get('/dd/aa/(\d+)', ':example:test:k1');
$router->get('/dd/(\d+)', ':example:test:k1');
$router->get('/dd/aa/bb/(\d+)/(\d+)', function($start=0, $end=0){
    echo "------------ 路由执行开始 ----------\n";
    for ($i=$start; $i<$end; $i++) {
        echo "this is ".$i."\n";
    }
    echo "end\n";
    echo "------------ 路由执行结速 ----------\n";
});
//------- 路由注册结束 ---------

$tmpfile = tempnam(sys_get_temp_dir(), 'test_');

//保存编译好的数据
//当你这样使用时，tmpfile一般是要加入版本管理的
//而这个生成文件要单独写一个文件，每次修改这个
//文件后重新运行一次
$tool = new \Bybzmt\Router\Tool($router->getRoutes());
$code = $tool->exportRoutes();

file_put_contents($tmpfile, $code);

//缓存的代码可以从这儿看
//var_dump($code);

#----- 正式程序将这么写 ----------
//直接读取之前保存的数据
$router2 = new \Bybzmt\Router\Router(require $tmpfile);

//执行路由动作
$router2->run();
#----- 正式程序将就这么多 ----------

######################################
#--------- 反向路由也可以缓存掉 -----#

$code = $tool->exportReverse();
file_put_contents($tmpfile, $code);

//缓存的代码可以从这儿看
//var_dump($code);

#----- 正式程序将这么写 ----------
//直接读取之前保存的数据
$reverse = new \Bybzmt\Router\Reverse(require $tmpfile);

$uri = $reverse->buildUri('example:test', ['k1'=>'2008', 'k2'=>'09', 'k3'=>'31', 'k4'=>'k4']);

var_dump($uri);

#----- 正式程序将就这么多 ----------

//清理 可忽略
@unlink($tmpfile);
