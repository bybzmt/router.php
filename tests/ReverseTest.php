<?php

class ReverseTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $router = new \Bybzmt\Router\Router();

        $router->get('/a1', ':example:test');
        $router->get('/a2/(\d+)', ':example:test:k1');
        $router->get('/a3/(\d+)/(\d+)', ':example:test:k1:k2');
        $router->get('/a4/(\d+)', ':example:test2:k1');
        $router->get('/a5/(\d+)', ':example:test2:k1');
        $router->get('/a6/(\d+)', ':example:test3:k1');
        $router->get('/optiona2/news(\(;¬_¬\)\d+(\(;¬_¬\)\d+)?)?', ':example2:test:(;¬_¬) d1:(;¬_¬) d2');

        $tool = new \Bybzmt\Router\Tool($router->getRoutes());
        $data = $tool->convertReverse();

        $this->reverse = new \Bybzmt\Router\Reverse($data);
    }

    protected function tearDown()
    {
    }

    public function testInit()
    {
        $this->assertInstanceOf('\Bybzmt\Router\Reverse', new \Bybzmt\Router\Reverse());
    }

    public function testBuildUriRoutes()
    {
        $uri = $this->reverse->buildUri('example:test');
        $this->assertEquals($uri, '/a1');

        $uri = $this->reverse->buildUri('example:test', ['k1'=>'2008']);
        $this->assertEquals($uri, '/a2/2008');

        $uri = $this->reverse->buildUri('example:test', ['k1'=>'2008', 'k2'=>'09']);
        $this->assertEquals($uri, '/a3/2008/09');

        $uri = $this->reverse->buildUri('example:test', ['k1'=>'2008', 'k2'=>'09', 'k3'=>'31']);
        $this->assertEquals($uri, '/a3/2008/09?k3=31');

        $uri = $this->reverse->buildUri('example:test', ['k1'=>'word']);
        $this->assertEquals($uri, '/a1?k1=word');
    }

    public function testBuildUriMetaChar()
    {
        $uri = $this->reverse->buildUri('example2:test', ['d1'=>'2008', 'd2'=>'09']);
        $this->assertEquals($uri, '/optiona2/news(;¬_¬)2008(;¬_¬)09');
    }

    /**
     * @expectedException Bybzmt\Router\Exception
     * @expectedExceptionMessage mkUrl 映射关系:example:test_not_map 未定义
     */
    public function testBuildUriNotMap()
    {
        $this->reverse->buildUri('example:test_not_map');
    }

    /**
     * @expectedException Bybzmt\Router\Exception
     * @expectedExceptionMessage 与所有规则都不匹配
     */
    public function testBuildUriNotMatch()
    {
        $uri = $this->reverse->buildUri('example:test2', ['k1'=>'not number']);
    }

    /**
     * @expectedException Bybzmt\Router\Exception
     * @expectedExceptionMessage 生成链接 缺少参数
     */
    public function testBuildUriNotKey()
    {
        $this->reverse->buildUri('example:test3', ['k2'=>'not hava k1']);
    }

}

