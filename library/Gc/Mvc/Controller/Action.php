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
 * @category   Gc
 * @package    Library
 * @subpackage Mvc\Controller
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

namespace Gc\Mvc\Controller;

use Gc\Event\StaticEventManager;
use Gc\Module\Model as ModuleModel;
use Gc\User\Model as UserModel;
use Gc\User\Acl;
use Gc\Registry;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Storage;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\MvcEvent;
use Zend\Session\Container as SessionContainer;
use Zend\View\Model\JsonModel;
use Zend\I18n\Translator\Translator;

/**
 * Extension of AbstractActionController
 *
 * @category   Gc
 * @package    Library
 * @subpackage Mvc\Controller
 */
class Action extends AbstractActionController
{
    /**
     * Route available for installer
     *
     * @var array
     */
    protected $installerRoutes = array(
        'install',
        'installCheckConfig',
        'installLicense',
        'installDatabase',
        'installConfiguration',
        'installComplete'
    );

    /**
     * Authentication service
     *
     * @var \Zend\Authentication\AuthenticationService
     */
    protected $auth = null;

    /**
     * RouteMatch
     *
     * @var \Zend\Mvc\Router\Http\RouteMatch
     */
    protected $routeMatch = null;

    /**
     * Session storage
     *
     * @var \Zend\Session\Storage\SessionStorage
     */
    protected $session = null;

    /**
     * User acl
     *
     * @var \Gc\User\Acl
     */
    protected $acl = null;

    /**
     * Execute the request
     *
     * @param MvcEvent $e Mvc Event
     *
     * @return mixed
     */
    public function onDispatch(MvcEvent $e)
    {
        $resultResponse = $this->_construct();
        if (!empty($resultResponse)) {
            return $resultResponse;
        }

        $this->init();
        return parent::onDispatch($e);
    }

    /**
     * Initiliaze
     *
     * @return void
     */
    public function init()
    {

    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $module    = $this->getRouteMatch()->getParam('module');
        $routeName = $this->getRouteMatch()->getMatchedRouteName();

        /**
         * Installation check, and check on removal of the install directory.
         */
        if (!file_exists(GC_APPLICATION_PATH . '/config/autoload/global.php')
            and !in_array($routeName, $this->installerRoutes)
        ) {
            return $this->redirect()->toRoute('install');
        } elseif (!in_array($routeName, $this->installerRoutes)) {
            $auth = $this->getAuth();
            if (!$auth->hasIdentity()) {
                if (!in_array(
                    $routeName,
                    array(
                        'userLogin',
                        'userForgotPassword',
                        'userForgotPasswordKey',
                        'renderWebsite'
                    )
                )
                ) {
                    return $this->redirect()->toRoute(
                        'userLogin',
                        array('redirect' => base64_encode($this->getRequest()->getRequestUri()))
                    );
                }
            } else {
                $userModel = $auth->getIdentity();

                $this->acl   = new Acl($userModel);
                $permissions = $userModel->getRole(true)->getUserPermissions();
                if ($routeName != 'userForbidden') {
                    if (!empty($this->aclPage)) {
                        $isAllowed = false;
                        if ($this->aclPage['resource'] == 'Modules') {
                            $moduleId = $this->getRouteMatch()->getParam('m');
                            if (empty($moduleId)) {
                                $action    = $this->getRouteMatch()->getParam('action');
                                $action    = ($action === 'index' ? 'list' : $action);
                                $isAllowed = $this->acl->isAllowed(
                                    $userModel->getRole()->getName(),
                                    $this->aclPage['resource'],
                                    $action
                                );
                            } else {
                                $moduleModel = ModuleModel::fromId($moduleId);
                                if (!empty($moduleModel)) {
                                    $isAllowed = $this->acl->isAllowed(
                                        $userModel->getRole()->getName(),
                                        $this->aclPage['resource'],
                                        $moduleModel->getName()
                                    );
                                }
                            }
                        } else {
                            $isAllowed = $this->acl->isAllowed(
                                $userModel->getRole()->getName(),
                                $this->aclPage['resource'],
                                $this->aclPage['permission']
                            );
                        }

                        if (!$isAllowed) {
                            return $this->redirect()->toRoute('userForbidden');
                        }
                    }

                }
                $this->layout()->adminUser = $userModel;
            }
        }

        $this->layout()->module  = strtolower($module);
        $this->layout()->version = \Gc\Version::VERSION;

        $this->useFlashMessenger(false);
        if (!in_array($routeName, $this->installerRoutes)
            and !in_array($routeName, array('userLogin', 'userForgotPassword', 'renderWebsite'))
        ) {
            /**
             * Prepare all resources
             */
            $helperBroker = $this->getServiceLocator()->get('ViewHelperManager');
            $headscript   = $helperBroker->get('HeadScript');
            $cdn          = $helperBroker->get('CdnBackend');
            $headscript
                ->appendFile($cdn('/backend/js/libs/modernizr-2.6.2.min.js'), 'text/javascript')
                ->appendFile($cdn('/backend/js/libs/jquery-1.9.1.js'), 'text/javascript')
                ->appendFile($cdn('/backend/js/libs/jquery.browser.js'), 'text/javascript')
                ->appendFile($cdn('/backend/js/plugins.js'), 'text/javascript')
                ->appendFile($cdn('/backend/js/libs/jquery-ui-1.10.1.custom.min.js'), 'text/javascript')
                ->appendFile($cdn('/backend/js/libs/codemirror/lib/codemirror.js'), 'text/javascript')
                ->appendFile($cdn('/backend/js/libs/codemirror/mode/xml/xml.js'), 'text/javascript')
                ->appendFile($cdn('/backend/js/libs/codemirror/mode/javascript/javascript.js'), 'text/javascript')
                ->appendFile($cdn('/backend/js/libs/codemirror/mode/css/css.js'), 'text/javascript')
                ->appendFile($cdn('/backend/js/libs/codemirror/mode/clike/clike.js'), 'text/javascript')
                ->appendFile($cdn('/backend/js/libs/codemirror/mode/php/php.js'), 'text/javascript')
                ->appendFile($cdn('/backend/js/libs/jquery.jstree.js'), 'text/javascript')
                ->appendFile($cdn('/backend/js/libs/jquery.contextMenu.js'), 'text/javascript')
                ->appendFile($cdn('/backend/js/libs/dropdown.js'), 'text/javascript')
                ->appendFile($cdn('/backend/js/generic-classes.js'), 'text/javascript')
                ->appendFile($cdn('/backend/js/gotcms.js'), 'text/javascript');

            $headlink = $helperBroker->get('HeadLink');
            $headlink
                ->appendStylesheet($cdn('/backend/css/normalize.css'))
                ->appendStylesheet($cdn('/backend/js/libs/codemirror/lib/codemirror.css'))
                ->appendStylesheet($cdn('/backend/css/jquery-ui-1.10.1.custom.css'))
                ->appendStylesheet($cdn('/backend/css/jquery.treeview.css'))
                ->appendStylesheet($cdn('/backend/css/elfinder.min.css'))
                ->appendStylesheet($cdn('/backend/css/jquery.contextMenu.css'))
                ->appendStylesheet($cdn('/backend/css/dropdown.css'))
                ->appendStylesheet($cdn('/backend/css/style.css'));
        }
    }

    /**
     * Return matched route
     *
     * @return \Zend\Mvc\Router\Http\RouteMatch
     */
    public function getRouteMatch()
    {
        if (empty($this->routeMatch)) {
            $this->routeMatch = $this->getEvent()->getRouteMatch();
        }

        return $this->routeMatch;
    }

    /**
     * Get session storage
     *
     * @return \Zend\Session\Storage\SessionStorage
     */
    public function getSession()
    {
        if ($this->session === null) {
            $this->session = new SessionContainer();
        }

        return $this->session;
    }

    /**
     * Get authentication
     *
     * @return \Zend\Authentication\AuthenticationService
     */
    public function getAuth()
    {
        if ($this->auth === null) {
            $this->auth = new AuthenticationService(new Storage\Session(UserModel::BACKEND_AUTH_NAMESPACE));
        }

        return $this->auth;
    }

    /**
     * Return json model
     *
     * @param array $data Data
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function returnJson(array $data)
    {
        $jsonModel = new JsonModel();
        $jsonModel->setVariables($data);
        $jsonModel->setTerminal(true);

        return $jsonModel;
    }

    /**
     * Initiliaze flash messenger
     *
     * @param boolean $forceDisplay Force display
     *
     * @return void
     */
    public function useFlashMessenger($forceDisplay = true)
    {
        $flashMessenger = $this->flashMessenger();
        $flashMessages  = array();
        foreach (array('error', 'success', 'info', 'warning') as $namespace) {
            $flashNamespace = $flashMessenger->setNameSpace($namespace);
            if ($forceDisplay) {
                if ($flashNamespace->hasCurrentMessages()) {
                    $flashMessages[$namespace] = $flashNamespace->getCurrentMessages();
                    $flashNamespace->clearCurrentMessages();
                }
            } else {
                if ($flashNamespace->hasMessages()) {
                    $flashMessages[$namespace] = $flashNamespace->getMessages();
                }
            }
        }

        $this->layout()->flashMessages = $flashMessages;
    }

    /**
     * Retrieve event manager
     *
     * @return \Gc\Event\StaticEventManager
     */
    public function events()
    {
        return StaticEventManager::getInstance();
    }
}
