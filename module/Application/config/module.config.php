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
 * @category   Gc_Application
 * @package    Application
 * @subpackage Config
 * @author     Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license    GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link       http://www.got-cms.com
 */

return array(
    'controllers' => array(
        'invokables' => array(
            'IndexController' => 'Application\Controller\IndexController',
            'InstallController' => 'Application\Controller\InstallController',
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
        ),
    ),
    'translator' => array(
        'locale' => 'en_GB',
        'translation_file_patterns' => array(
            array(
                'type'     => 'phparray',
                'base_dir' => GC_APPLICATION_PATH . '/data/translation',
                'pattern'  => '%s.php',
            ),
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason'  => false,
        'display_exceptions'        => false,
        'doctype'                   => 'HTML5',
        'not_found_template'        => 'error/404',
        'exception_template'        => 'error/index',
        'template_map' => array(
            'layout/layout'     => __DIR__ . '/../views/layouts/layout.phtml',
            'error/404'         => __DIR__ . '/../views/error/404.phtml',
            'error/index'       => __DIR__ . '/../views/error/index.phtml',
        ),
        'template_path_stack' => array(
            'application' => __DIR__ . '/../views',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'cdn'               => 'Gc\View\Helper\Cdn',
            'cdnBackend'        => 'Gc\View\Helper\CdnBackend',
            'documents'         => 'Gc\View\Helper\Documents',
            'document'          => 'Gc\View\Helper\Document',
            'formCheckbox'      => 'Gc\View\Helper\FormCheckbox',
            'formMultiCheckbox' => 'Gc\View\Helper\FormMultiCheckbox',
            'moduleUrl'         => 'Gc\View\Helper\ModuleUrl',
            'partial'           => 'Gc\View\Helper\Partial',
            'script'            => 'Gc\View\Helper\Script',
            'tools'             => 'Gc\View\Helper\Tools',
            'modulePlugin'      => 'Gc\View\Helper\ModulePlugin',
        ),
    ),
);
