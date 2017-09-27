<?php

class BasicTest extends PHPUnit_Framework_TestCase
{
    protected $content;

    protected function setUp()
    {
        $this->content = 'this time is: ' . date('Y-m-d H:i:s');
    }

    protected function tearDown()
    {
        // nothing
    }

    public function testInit()
    {
        $this->assertInstanceOf('\Bybzmt\Router\Basic', new \Bybzmt\Router\Basic());
    }

    public function testStaticHome()
    {
        $router = new \Bybzmt\Router\Basic();

        $router->handle('GET', '/', $this->content);
        $re = $router->match('GET', '/');
        $this->assertEquals($re, [$this->content, []]);
    }

    public function testStaticRoute()
    {
        $router = new \Bybzmt\Router\Basic();

        $router->handle('GET', '/about', $this->content);
        $re = $router->match('GET', '/about');
        $this->assertEquals($re, [$this->content, []]);

        $router->handle('GET', '/about1/', $this->content);
        $re = $router->match('GET', '/about1/');
        $this->assertEquals($re, [$this->content, []]);

        $router->handle('GET', '/about3/', $this->content);
        $re = $router->match('GET', '/about3');
        $this->assertEquals($re, [$this->content, []]);

        $router->handle('GET', '/about4', $this->content);
        $re = $router->match('GET', '/about4/');
        $this->assertEquals($re, [$this->content, []]);
    }

    public function testRequestMethod()
    {
        $router = new \Bybzmt\Router\Basic();

        $router->handle('POST', '/about', $this->content);
        $re = $router->match('POST', '/about');
        $this->assertEquals($re, [$this->content, []]);

        $router->handle('PUT', '/about', $this->content);
        $re = $router->match('PUT', '/about');
        $this->assertEquals($re, [$this->content, []]);
    }

    public function testDynamicRoute()
    {
        $router = new \Bybzmt\Router\Basic();
        $router->handle('GET', '/hello/(\w+)', $this->content);

        $re = $router->match('GET', '/hello/bramus');
        $this->assertEquals($re, [$this->content, ['bramus']]);
    }

    public function testDynamicRouteWithMultiple()
    {
        $router = new \Bybzmt\Router\Basic();
        $router->handle('GET', '/hello/(\w+)/(\w+)', $this->content);

        $re = $router->match('GET', '/hello/bramus/sumarb');
        $this->assertEquals($re, [$this->content, ['bramus', 'sumarb']]);
    }

    public function testDynamicRouteWithOptionalSubpatterns()
    {
        $router = new \Bybzmt\Router\Basic();
        $router->handle('GET', '/hello(/\w+)?', $this->content);

        $re = $router->match('GET', '/hello');
        $this->assertEquals($re, [$this->content, []]);

        $re = $router->match('GET', '/hello/bramus');
        $this->assertEquals($re, [$this->content, ['/bramus']]);
    }

    public function testDynamicRouteWithMultipleSubpatterns()
    {
        $router = new \Bybzmt\Router\Basic();

        $router->handle('GET', '/(.*)/page([0-9]+)', $this->content);

        $re = $router->match('GET', '/hello/bramus/page3');
        $this->assertEquals($re, [$this->content, ['hello/bramus', '3']]);
    }

    public function testDynamicRouteWithOptionalNestedSubpatterns()
    {
        $router = new \Bybzmt\Router\Basic();

        $router->handle('GET', '/blog(/\d{4}(/\d{2}(/\d{2}(/[a-z0-9_-]+)?)?)?)?', $this->content);

        $re = $router->match('GET', '/blog');
        $this->assertEquals($re, [$this->content, []]);

        $re = $router->match('GET', '/blog/1983');
        $this->assertEquals($re, [$this->content, ['/1983']]);

        $re = $router->match('GET', '/blog/1983/12');
        $this->assertEquals($re, [$this->content, ['/1983', '/12']]);

        $re = $router->match('GET', '/blog/1983/12/26');
        $this->assertEquals($re, [$this->content, ['/1983', '/12', '/26']]);

        $re = $router->match('GET', '/blog/1983/12/26/bramus');
        $this->assertEquals($re, [$this->content, ['/1983', '/12', '/26', '/bramus']]);
    }

    public function testDynamicRouteWithNestedOptionalSubpatterns()
    {
        $router = new \Bybzmt\Router\Basic();

        $router->handle('GET', '/hello(/\w+(/\w+)?)?', $this->content);

        $re = $router->match('GET', '/hello/bramus');
        $this->assertEquals($re, [$this->content, ['/bramus']]);

        $re = $router->match('GET', '/hello/bramus/bramus');
        $this->assertEquals($re, [$this->content, ['/bramus', '/bramus']]);
    }

    public function testDynamicRouteWithWildcard()
    {
        $router = new \Bybzmt\Router\Basic();
        $router->handle('GET', '(.*)', $this->content);

        $re = $router->match('GET', '/hello/bramus');
        $this->assertEquals($re, [$this->content, ['hello/bramus']]);
    }

    public function testDynamicRouteWithPartialWildcard()
    {
        $router = new \Bybzmt\Router\Basic();
        $router->handle('GET', '/hello/(.*)', $this->content);

        $re = $router->match('GET', '/hello/bramus/sumarb');
        $this->assertEquals($re, [$this->content, ['bramus/sumarb']]);
    }

    public function testStaticNotFound()
    {
        $router = new \Bybzmt\Router\Basic();

        $router->handle('GET', '/about', $this->content);

        $re = $router->match('GET', '/about2');
        $this->assertEquals(null, $re);

        $router->handle('GET', '/about/about', $this->content);

        $re = $router->match('GET', '/about/about2');
        $this->assertEquals(null, $re);
    }

    public function testRequestMethodNotFound()
    {
        $router = new \Bybzmt\Router\Basic();
        $router->handle('GET', '/about', $this->content);

        $re = $router->match('POST', '/about');
        $this->assertEquals(null, $re);
    }

    public function testDynamicNotFound()
    {
        $router = new \Bybzmt\Router\Basic();
        $router->handle('GET', '/hello/(\d+)', $this->content);

        $re = $router->match('GET', '/hello/bramus');
        $this->assertEquals(null, $re);
    }

    public function testDynamicMethodNotFound()
    {
        $router = new \Bybzmt\Router\Basic();
        $router->handle('GET', '/hello/(\w+)', $this->content);

        $re = $router->match('POST', '/hello/bramus');
        $this->assertEquals(null, $re);
    }


}

