<?php

class ToolTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->_tmpfile = tempnam(sys_get_temp_dir(), 'test_');

        $router = new \Bybzmt\Router\Router();

        $router->get('/aa', function(){ echo "aa\n"; });
        $router->get('/bb', function(){ echo "aa\n"; });
        $router->get('/dd/aa', ':example.test');
        $router->get('/dd/aa(/\d{4}(/\d{2}(/\d{2})?)?)?', ':example.test:/ k1:/ k2:/ k3');
        $router->get('/dd/aa/(\d+)', ':example.test:k1');
        $router->get('/dd/(\d+)', ':example.test:k1');
        $router->get('/dd/aa/bb/(\d+)/(\d+)', function($start=0, $end=0){
            echo "------------ 路由执行开始 ----------\n";
            for ($i=$start; $i<$end; $i++) {
                echo "this is ".$i."\n";
            }
            echo "end\n";
            echo "------------ 路由执行结速 ----------\n";
        });

        $this->_router = $router;
    }

    protected function tearDown()
    {
        @unlink($this->_tmpfile);
        // nothing
    }

    public function testInit()
    {
        $this->assertInstanceOf('\Bybzmt\Router\Tool', new \Bybzmt\Router\Tool());
    }

    public function testExportRoutes()
    {
        $data = $this->_router->getRoutes();

        $tool = new \Bybzmt\Router\Tool($data);
        $code = $tool->exportRoutes();

        file_put_contents($this->_tmpfile, $code);

        $new = require $this->_tmpfile;

        $this->assertEquals($data, $new);
    }

    public function testExportReverse()
    {
        $data = $this->_router->getRoutes();

        $tool = new \Bybzmt\Router\Tool($data);
        $code = $tool->exportReverse();

        file_put_contents($this->_tmpfile, $code);

        $reverse = new \Bybzmt\Router\Reverse(require $this->_tmpfile);

        $uri = $reverse->buildUri('example.test', ['k1'=>'2008', 'k2'=>'09', 'k3'=>'31', 'k4'=>'k4']);

        $this->assertEquals($uri, '/dd/aa/2008/09/31?k4=k4');
    }

    /**
     * @expectedException Bybzmt\Router\Exception
     * @expectedExceptionMessage 与映射key数量不符
     */
    public function testConvertReverseKeyNum()
    {
        $router = new \Bybzmt\Router\Router();
        $router->get('/a3/(\d+)/(\d+)', ':example.test:k1');

        $tool = new \Bybzmt\Router\Tool($router->getRoutes());
        $data = $tool->convertReverse();
    }

    /**
     * @expectedException Bybzmt\Router\Exception
     * @expectedExceptionMessage 与映射key数量不符
     */
    public function testConvertReverseNoKey()
    {
        $router = new \Bybzmt\Router\Router();
        $router->get('/a3/(\d+)/(\d+)', ':example.test');

        $tool = new \Bybzmt\Router\Tool($router->getRoutes());
        $data = $tool->convertReverse();
    }

    /**
     * @expectedException Bybzmt\Router\Exception
     * @expectedExceptionMessage 与映射key数量不符
     */
    public function testConvertReverseBadFunc()
    {
        $router = new \Bybzmt\Router\Router();
        $router->get('/a3/(\d+)/(\d+)', ':example');

        $tool = new \Bybzmt\Router\Tool($router->getRoutes());
        $data = $tool->convertReverse();
    }
}

