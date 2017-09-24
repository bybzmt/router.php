<?php

class RouterTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        // Default request method to GET
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $_SERVER['REQUEST_URI'] = '/';

        // Default SERVER_PROTOCOL method to HTTP/1.1
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
    }

    protected function tearDown()
    {
        // nothing
    }

    public function testInit()
    {
        $this->assertInstanceOf('\Bybzmt\Router\Router', new \Bybzmt\Router\Router());
    }

    public function testStaticRoute()
    {
        $router = new \Bybzmt\Router\Router();
        $_SERVER['REQUEST_URI'] = '/about';
        $router->handle('GET', '/about', function () { return 'about'; });
        $this->assertEquals('about', $router->run());
    }

    public function testDynamicRoute()
    {
        $router = new \Bybzmt\Router\Router();
        $router->get('/hello/(\w+)/(\w+)', function ($name, $lastname) {
            return 'Hello ' . $name . ' ' . $lastname;
        });

        $_SERVER['REQUEST_URI'] = '/hello/bramus/sumarb';

        $this->assertEquals('Hello bramus sumarb', $router->run());
    }

    public function testMethodHead()
    {
        $router = new \Bybzmt\Router\Router();
        $router->get('/', function () {
            return 'method';
        });

        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['REQUEST_METHOD'] = 'HEAD';

        $this->assertEquals('method', $router->run());
    }

    public function testRequestMethod()
    {
        $_SERVER['REQUEST_URI'] = '/';

        $router = new \Bybzmt\Router\Router();
        $router->get('/', function () { return 'get'; });
        $router->post('/', function () { return 'post'; });
        $router->put('/', function () { return 'put'; });
        $router->delete('/', function () { return 'delete'; });
        $router->patch('/', function () { return 'patch'; });
        $router->options('/', function () { return 'options'; });

        $router->setMethod('HEAD');
        $this->assertEquals('get', $router->run());

        $router->setMethod('GET');
        $this->assertEquals('get', $router->run());

        $router->setMethod('POST');
        $this->assertEquals('post', $router->run());

        $router->setMethod('PUT');
        $this->assertEquals('put', $router->run());

        $router->setMethod('DELETE');
        $this->assertEquals('delete', $router->run());

        $router->setMethod('PATCH');
        $this->assertEquals('patch', $router->run());

        $router->setMethod('OPTIONS');
        $this->assertEquals('options', $router->run());
    }

    public function testShorthandAll()
    {
        $_SERVER['REQUEST_URI'] = '/';

        $router = new \Bybzmt\Router\Router();
        $router->all('/', function () {
            return 'all';
        });

        $router->setMethod('HEAD');
        $this->assertEquals('all', $router->run());

        $router->setMethod('GET');
        $this->assertEquals('all', $router->run());

        $router->setMethod('POST');
        $this->assertEquals('all', $router->run());

        $router->setMethod('PUT');
        $this->assertEquals('all', $router->run());

        $router->setMethod('DELETE');
        $this->assertEquals('all', $router->run());

        $router->setMethod('PATCH');
        $this->assertEquals('all', $router->run());

        $router->setMethod('OPTIONS');
        $this->assertEquals('all', $router->run());
    }

    /**
     * @runInSeparateProcess
     */
    public function testDefault404()
    {
        $_SERVER['REQUEST_URI'] = '/foo';

        $router = new \Bybzmt\Router\Router();
        $router->get('/', function () {
            echo 'home';
        });

        $this->expectOutputString('404 page not found');
        $router->run();
    }

    public function test404()
    {
        $_SERVER['REQUEST_URI'] = '/foo';

        $router = new \Bybzmt\Router\Router();
        $router->set404(function () {
            return 'route not found';
        });

        $this->assertEquals('route not found', $router->run());
    }

    public function testHttpMethodOverride()
    {
        // Fake the request method to being POST and override it
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'PUT';

        $router = new \Bybzmt\Router\Router();
        $this->assertEquals('PUT', $router->getMethod());

        //GET时X不起作用
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] = 'PUT';

        $router = new \Bybzmt\Router\Router();
        $this->assertEquals('GET', $router->getMethod());
    }
}

