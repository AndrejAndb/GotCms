<?php
/**
 * This source file is part of GotCms.
 *
 * GotCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GotCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with GotCms. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category Gc_Tests
 * @package  Library
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Gc\View\Helper;

use Zend\View\Helper\Url as UrlHelper;
use Zend\Mvc\Router\RouteMatch;
use Zend\Mvc\Router\SimpleRouteStack as Router;
use Zend\View\Renderer\PhpRenderer as View;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-10-17 at 20:40:08.
 *
 * @group Gc
 * @category Gc_Tests
 * @package  Library
 */
class ModuleUrlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ModuleUrl
     *
     * @return void
     */
    protected $object;

    /**
     * @var Router
     *
     * @return void
     */
    protected $router;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->object = new ModuleUrl;
        $router       = new Router();
        $router->addRoute(
            'moduleEdit',
            array(
                'type' => 'Zend\Mvc\Router\Http\Segment',
                'options' => array(
                    'route' => '/admin/module/:m[/:mc[/:ma]]',
                    'defaults' => array(
                        'm' => '1'
                    )
                )
            )
        );

        $this->router = $router;
        $view         = new View;
        $view->getHelperPluginManager();
        $this->object->setView($view);
        $this->object->getView()->plugin('url')->setRouter($router);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
        unset($this->object);
        unset($this->router);
    }

    /**
     * Test
     *
     * @covers Gc\View\Helper\ModuleUrl::__invoke
     *
     * @return void
     */
    public function testRoute()
    {
        $url = $this->object->__invoke();
        $this->assertEquals('/admin/module/1', $url);
    }

    /**
     * Test
     *
     * @covers Gc\View\Helper\ModuleUrl::__invoke
     *
     * @return void
     */
    public function testRouteWithControllerAndAction()
    {
        $this->assertEquals('/admin/module/1/index/index', $this->object->__invoke('index', 'index'));
    }

    /**
     * Test
     *
     * @covers Gc\View\Helper\ModuleUrl::__invoke
     *
     * @return void
     */
    public function testRouteWithParams()
    {
        $this->assertEquals(
            '/admin/module/1/index/index?key=value',
            $this->object->__invoke('index', 'index', array('key' => 'value'))
        );
    }
}
