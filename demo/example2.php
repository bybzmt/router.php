<?php

//载入类文件
//实际使用中请通过composer安装，它有自动载入功能
require __DIR__ . '/loader.php';

//模拟请求
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/callback/a1/b2';

//------------------------

$router = new \Bybzmt\Router\Router();

/*
 * 只要是合法的Callable类型的值都可以作为回调设置
 */
$router->get('/', function(){
    echo "首页\n";
});

/*
 * 回调函数可以有参数
 * 捕获到的参数会按顺序传给回调函数
 * 注意！参数必需都有默认值，防止因缺少参数出现代码报错！
 *
 * 另外个人不太推荐用这样的参数形式
 * 应该优先使用example1.php的方式设置
 * 1. 那样初始化性能更好(其它不需要用的路由仅仅是字符串而己)
 * 2. 通过$_GET获取值参数与其它参数形式统一
 * 3. 方使去写一个mkurl()之类的函数去拼装链接，当链接有变动时直接修改函数即可
 */
$router->get('/callback/(\w+)/(\w+)', function($aa=null, $bb=null){
    echo "参数: $aa, $bb\n";
});

/*
 * 这种形多更方便
 */
$router->get('/callback2/(\w+)/(\w+)', ':example:test:k1:k2');

//执行路由动作
$router->run();

//-------------------------
class Example
{
    public function test()
    {
        echo "example::test in " . $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'];
        echo "\n";
        var_dump($_GET);
        echo "\n";
    }
}

/*
 * 这是一个mkUrl的示例
 */
function mkUrl(string $action, array $params=[])
{
    //静态映射可由Router数据直接生成
    $static = [
        'example:static' => '/home',
    ];

    $map = [
        'example:test' => ['/callback2/%s/%s', ['k1', 'k2']],
    ];

    if (isset($static[$action])) {
        $url = $static[$action];
        if ($params) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }

    if (isset($map[$action])) {
        list($format, $keys) = $map[$action];

        $param_arr = [$format];

        foreach ($keys as $key) {
            if (isset($params[$key])) {
                $param_arr[] = $params[$key];
                unset($params[$key]);
            } else {
                throw new Exception("mkUrl $action 参数: $key 缺少");
            }
        }

        $url = call_user_func_array('sprintf', $param_arr);
        if ($params) {
            $url .= '?' . http_build_query($params);
        }
        return $url;
    }

    throw new Exception("mkUrl 映射关系:$action 未定义");
}
