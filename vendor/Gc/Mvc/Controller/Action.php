<?php
/**
 * This source file is part of Got CMS.
 *
 * Got CMS is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Got CMS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with Got CMS. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category    Gc
 * @package     Library
 * @subpackage  Mvc\Controller
 * @author      Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license     GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link        http://www.got-cms.com
 */

namespace Gc\Mvc\Controller;

use Gc\User\Model,
    Gc\User\Acl,
    Zend\Authentication\AuthenticationService,
    Zend\Mvc\Controller\ActionController,
    Zend\Mvc\MvcEvent,
    Zend\Session\Container as SessionContainer,
    Zend\View\Model\JsonModel;

class Action extends ActionController
{
    /**
     * @var array route available for installer
     */
    protected $_installerRoutes = array('install', 'installCheckConfig', 'installLicense', 'installDatabase', 'installConfiguration', 'installComplete');

    /**
     * @var \Zend\Authentication\AuthenticationService
     */
    protected $_auth = NULL;

    /**
     * @var \Zend\Mvc\Router\Http\RouteMatch
     */
    protected $_routeMatch = NULL;

    /**
     * @var \Zend\Session\Storage\SessionStorage
     */
    protected $_session = NULL;

    /**
     * @var \Gc\User\Acl
     */
    protected $_acl = NULL;

    /**
     * Execute the request
     *
     * @param  MvcEvent $e
     * @return mixed
     */
    public function execute(MvcEvent $e)
    {
        $result_response = $this->_construct();
        if(!empty($result_response))
        {
            return $result_response;
        }

        $this->init();
        return parent::execute($e);
    }

    /**
     * Initiliaze
     * @return void
     */
    public function init()
    {

    }

    /**
     * Constructor
     * @return void
     */
    protected function _construct()
    {
        $module = $this->getRouteMatch()->getParam('module');
        $route_name = $this->getRouteMatch()->getMatchedRouteName();

        /**
         * Installation check, and check on removal of the install directory.
         */
        if (!file_exists(GC_APPLICATION_PATH . '/config/autoload/global.php') and !in_array($route_name, $this->_installerRoutes))
        {
            return $this->redirect()->toRoute('install');
        }
        elseif(!in_array($route_name, $this->_installerRoutes))
        {
            $auth = $this->getAuth();
            if(!$auth->hasIdentity())
            {
                if(!in_array($route_name, array('userLogin', 'userForgotPassword', 'renderWebsite')))
                {
                    return $this->redirect()->toRoute('userLogin', array('redirect' => base64_encode($this->getRequest()->getRequestUri())));
                }
            }
            else
            {
                $user_model = $auth->getIdentity();

                $this->_acl = new Acl($user_model);
                $permissions = $user_model->getRole()->getUserPermissions();

                if($route_name != 'userForbidden' and !empty($this->_acl_page) and !$this->_acl->isAllowed($user_model->getRole()->getName(), $this->_acl_page['resource'], $this->_acl_page['permission']))
                {
                    return $this->redirect()->toRoute('userForbidden');
                }
            }
        }

        $this->layout()->module = $module;
    }

    /**
     * Return matched route
     * @return \Zend\Mvc\Router\Http\RouteMatch
     */
    public function getRouteMatch()
    {
        if(empty($this->_routeMatch))
        {
            $this->_routeMatch = $this->getEvent()->getRouteMatch();
        }

        return $this->_routeMatch;
    }

    /**
     * Get session storage
     * @return \Zend\Session\Storage\SessionStorage
     */
    protected function getSession()
    {
        if($this->_session === NULL)
        {
            $this->_session = new SessionContainer();
        }

        return $this->_session;
    }

    /**
     * Get authentication
     * @return \Zend\Authentication\AuthenticationService
     */
    protected function getAuth()
    {
        if($this->_auth === NULL)
        {
            $this->_auth = new AuthenticationService();
        }

        return $this->_auth;
    }

    /**
     * Return json model
     * @return \Zend\View\Model\JsonModel
     */
    protected function _returnJson(array $data)
    {
        $json_model = new JsonModel();
        $json_model->setVariables($data);
        $json_model->setTerminal(TRUE);
        return $json_model;
    }
}
