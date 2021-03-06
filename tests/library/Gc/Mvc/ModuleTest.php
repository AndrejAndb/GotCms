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

namespace Gc\Mvc;

use Gc\Registry;
use Gc\Core\Config as CoreConfig;
use Gc\Layout\Model as LayoutModel;
use Gc\View\Stream;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use Zend\Mvc\Router\RouteMatch;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-10-17 at 20:40:11.
 *
 * @group Gc
 * @category Gc_Tests
 * @package  Library
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Module
     */
    protected $object;

    /**
     * @var Zend\Uri\Http
     */
    protected $uri;

    /**
     * @var Zend\Mvc\MvcEvent
     */
    protected $mvcEvent;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        include_once __DIR__ . '/ModuleUnit.php';
        $this->object   = new ModuleUnit;
        $this->uri      = Registry::get('Application')->getRequest()->getUri();
        $this->mvcEvent = Registry::get('Application')->getMvcEvent();
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
    }
    /**
     * Test
     *
     * @covers Gc\Mvc\Module::onBootstrap
     *
     * @return void
     */
    public function testOnBootstrap()
    {
        Registry::getInstance()->offsetUnset('Translator');
        $this->assertNull($this->object->onBootstrap(Registry::get('Application')->getMvcEvent()));
    }

    /**
     * Test
     *
     * @covers Gc\Mvc\Module::prepareException
     *
     * @return void
     */
    public function testPrepareException()
    {
        Stream::register();
        $layoutModel = LayoutModel::fromArray(
            array(
                'name' => 'Layout Name',
                'identifier' => 'Layout identifier',
                'description' => 'Layout Description',
                'content' => 'Layout Content'
            )
        );

        $layoutModel->save();
        $routeMatch = new RouteMatch(array());
        $routeMatch->setMatchedRouteName('renderWebsite');
        $this->mvcEvent->setRouteMatch($routeMatch);
        CoreConfig::setValue('site_exception_layout', $layoutModel->getId());
        $this->assertNull($this->object->prepareException(Registry::get('Application')->getMvcEvent()));
        $layoutModel->delete();
    }

    /**
     * Test
     *
     * @covers Gc\Mvc\Module::checkSsl
     *
     * @return void
     */
    public function testCheckSslWithFrontendRoute()
    {
        CoreConfig::setValue('force_frontend_ssl', 1);
        CoreConfig::setValue('secure_frontend_base_path', 'https://got-cms.com');
        $routeMatch = new RouteMatch(array());
        $routeMatch->setMatchedRouteName('renderWebsite');
        $this->mvcEvent->setRouteMatch($routeMatch);
        $oldScheme = $this->uri->getScheme();
        $result    = $this->object->checkSsl($this->mvcEvent);
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $result);
        $this->uri->setScheme($oldScheme);
        CoreConfig::setValue('secure_frontend_base_path', '');
    }

    /**
     * Test
     *
     * @covers Gc\Mvc\Module::checkSsl
     *
     * @return void
     */
    public function testCheckSslWithFrontendRouteAndAlreadyHttps()
    {
        CoreConfig::setValue('force_frontend_ssl', 1);
        $routeMatch = new RouteMatch(array());
        $routeMatch->setMatchedRouteName('renderWebsite');
        $this->mvcEvent->setRouteMatch($routeMatch);
        $oldScheme = $this->uri->getScheme();
        $this->uri->setScheme('https');
        $this->assertNull($this->object->checkSsl($this->mvcEvent));
        $this->uri->setScheme($oldScheme);
    }

    /**
     * Test
     *
     * @covers Gc\Mvc\Module::checkSsl
     *
     * @return void
     */
    public function testCheckSslWithoutForceRoute()
    {
        CoreConfig::setValue('force_frontend_ssl', 0);
        $routeMatch = new RouteMatch(array());
        $routeMatch->setMatchedRouteName('renderWebsite');
        $this->mvcEvent->setRouteMatch($routeMatch);
        $oldScheme = $this->uri->getScheme();
        $this->uri->setScheme('http');
        $this->assertNull($this->object->checkSsl($this->mvcEvent));
        $this->uri->setScheme($oldScheme);
    }

    /**
     * Test
     *
     * @covers Gc\Mvc\Module::checkSsl
     *
     * @return void
     */
    public function testCheckSslWithithForceBackendRoute()
    {
        CoreConfig::setValue('force_backend_ssl', 0);
        $routeMatch = new RouteMatch(
            array(
                'module' => 'Config',
                'controller' => 'UserController',
                'action' => 'login',
            )
        );
        $routeMatch->setMatchedRouteName('userLogin');
        $this->mvcEvent->setRouteMatch($routeMatch);
        $this->assertNull($this->object->checkSsl($this->mvcEvent));
    }

    /**
     * Test
     *
     * @covers Gc\Mvc\Module::checkSsl
     *
     * @return void
     */
    public function testCheckSslWithBackendRoute()
    {
        CoreConfig::setValue('force_backend_ssl', 1);
        CoreConfig::setValue('secure_backend_base_path', 'https://got-cms.com');
        $routeMatch = new RouteMatch(
            array(
                'module' => 'Config',
                'controller' => 'UserController',
                'action' => 'login',
            )
        );
        $routeMatch->setMatchedRouteName('userLogin');
        $this->mvcEvent->setRouteMatch($routeMatch);
        $oldScheme = $this->uri->getScheme();
        $result    = $this->object->checkSsl($this->mvcEvent);
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $result);
        $this->uri->setScheme($oldScheme);
        CoreConfig::setValue('secure_backend_base_path', '');
    }

    /**
     * Test
     *
     * @covers Gc\Mvc\Module::getAutoloaderConfig
     * @covers Gc\Mvc\Module::getDir
     * @covers Gc\Mvc\Module::getNamespace
     *
     * @return void
     */
    public function testGetAutoloaderConfig()
    {
        $this->assertInternalType('array', $this->object->getAutoloaderConfig());
    }

    /**
     * Test
     *
     * @covers Gc\Mvc\Module::getConfig
     *
     * @return void
     */
    public function testGetConfig()
    {
        CoreConfig::setValue('debug_is_active', 1);
        $this->assertInternalType('array', $this->object->getConfig());
    }

    /**
     * Test
     *
     * @covers Gc\Mvc\Module::init
     *
     * @return void
     */
    public function testInit()
    {
        $oldDatabase      = Registry::get('Db');
        $oldConfiguration = Registry::get('Configuration');
        $oldAdapter       = GlobalAdapterFeature::getStaticAdapter();

        if (!CoreConfig::getValue('session_lifetime')) {
            CoreConfig::getInstance()->insert(
                array(
                    'identifier' => 'session_lifetime',
                    'value'      => 3600,
                )
            );
        }

        if (!CoreConfig::getValue('cookie_domain')) {
            CoreConfig::getInstance()->insert(
                array(
                    'identifier' => 'cookie_domain',
                    'value'      => 'got-cms.com',
                )
            );
        }

        CoreConfig::setValue('session_handler', CoreConfig::SESSION_DATABASE);

        Registry::getInstance()->offsetUnset('Configuration');
        $this->assertNull($this->object->init(Registry::get('Application')->getServiceManager()->get('ModuleManager')));

        Registry::set('Db', $oldDatabase);
        Registry::set('Configuration', $oldConfiguration);
        GlobalAdapterFeature::setStaticAdapter($oldAdapter);
    }
}
