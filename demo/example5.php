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
$router->get('/dd/aa/(\d{4}(\d{2}(/\d{2})?)?)?\.php', ':example:test');
$router->get('/dd/aa/(\d{4}(-\d{2}(-\d{2})?)?)?\.php', ':example:test');
$router->get('/dd/aa/(\d+)', ':example:test');
$router->get('/dd/(\d+)', ':example:test');
$router->get('/cc', function(){
    $std = new StdClass();
    $std->aa = 1;
    var_dump($std);
});
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

echo "------------ 生成的代码开始 ----------\n";
echo $code;
echo "------------ 生成的代码结速 ----------\n";

#----- 正式程序将这么写 ----------
//直接读取之前保存的数据
$router2 = new \Bybzmt\Router\Router(require $tmpfile);

//执行路由动作
$router2->run();
#----- 正式程序将就这么多 ----------

//清理 可忽略
@unlink($tmpfile);
