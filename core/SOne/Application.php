<?php
/**
 * Copyright (C) 2012 Andrey F. Kupreychik (Foxel)
 *
 * This file is part of QuickFox SimpleOne.
 *
 * SimpleOne is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SimpleOne is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SimpleOne. If not, see <http://www.gnu.org/licenses/>.
 */

class SOne_Application extends K3_Application
{
    const DEFAULT_PLUGINS_SUBDIR = 'plugins';
    const EVENT_PAGE_RENDER = 'pageRender';
    const EVENT_WIDGETS_BOOTSTRAP = 'widgetsBootstrap';

    /**
     * @var K3_Config
     */
    protected $_config  = null;

    /**
     * @var FDataBase
     */
    protected $_db      = null;

    /**
     * @var SOne_Request
     */
    protected $_request = null;

    /**
     * @var FVISInterface
     */
    protected $_VIS     = null;

    /**
     * Objects repository
     * @var SOne_Repository_Object
     */
    protected $_objects = null; // objects repository

    /**
     * @var FLNGData
     */
    protected $_lang    = null;

    public function __construct(K3_Environment $env = null)
    {
        $this->_env = is_null($env) ? F()->appEnv : $env;

        $this->pool = array(
            'environment' => &$this->_env,
            'config'      => &$this->_config,
        );
    }

    public function bootstrap()
    {
        F()->Timer->logEvent('App Bootstrap start');

        $this->_config = new K3_Config($c = $this->_parseConfigLines((array) FMisc::loadDatafile(F_DATA_ROOT.DIRECTORY_SEPARATOR.'sone.qfc.php', FMisc::DF_SLINE)));

        // preparing DB
        $this->_db = F()->DBase; //new FDataBase('mysql');
        $this->_db->connect($this->_config->db);

        if ($this->_config->app->useTransaction) {
            $this->_db->beginTransaction();
            $this->getResponse()->addEventHandler('closeAndExit', array($this, 'commitOnResponseSent'));
        }

        $this->_env->session->setDBase($this->_db, 'sessions');

        $this->_request = new SOne_Request($this->_env, $this->_config);

        $this->_VIS = new FVISInterface($this->_env);
        $this->_VIS->addAutoLoadDir(F_DATA_ROOT.'/styles/simple')
        //    ->loadECSS(F_DATA_ROOT.'/styles/simple/common.ecss')
        ;
        F()->Parser->initStdTags();
        $this->_VIS->addFuncParser('BBPARSE', array(F()->Parser, 'parse'));

        $this->_objects = SOne_Repository_Object::getInstance($this->_db);

        $this->_lang = F()->LNG;
        $this->_lang->addAutoLoadDir(F_DATA_ROOT.DIRECTORY_SEPARATOR.'lang/ru');
        $this->_lang->timeZone = 7;

        // putting to environment
        $this->_env
            ->put('db',   $this->_db)
            ->put('VIS',  $this->_VIS)
            ->put('user', $this->_bootstrapUser())
            ->put('lang', $this->_lang)
            ->put('app',  $this);

        $this->bootstrapPlugins();

        F()->Timer->logEvent('App Bootstrap end');

        return $this;
    }

    public function commitOnResponseSent()
    {
        if ($this->_db->inTransaction) {
            $this->_db->commit();
        }
    }

    public function run()
    {
        $object = $this->routeRequest($this->_request, true);

        F()->Timer->logEvent('App Action end');

        $response = $this->renderPage($object);

        $this->getResponse()->clearBuffer()
            ->write($response)
            ->sendBuffer();
    }

    public function routeRequest(SOne_Request $request, $performAction = true)
    {
        /** @var $tipObject SOne_Model_Object */
        $tipObject = null;
        if ($staticRoutes = $this->_config->staticRoutes) {
            $staticRoutes = $staticRoutes->toArray();
            foreach ($staticRoutes as $route => $data) {
                if ($request->path == $route || strpos($request->path, $route.'/') === 0) {
                    if (!is_array($data)) {
                        $data = array(
                            'isStatic' => true,
                            'class'    => $data,
                            'path'     => $route,
                        );
                    } else {
                        $data['path'] = $route;
                        $data['isStatic'] = true;
                    }
                    $tipObject = SOne_Model_Object::construct($data);
                }
            }
        }

        if (!$tipObject) {
            $navis = $this->_objects->loadNavigationByPath($request->path);
            $tipObjectNavi = !empty($navis) ? end($navis) : null;
            $tipObject = $tipObjectNavi ? $this->_objects->loadOne($tipObjectNavi['id']) : null;
        }


        if (($tipObject instanceof SOne_Model_Object) && (trim($tipObject->path, '/') == $request->path)) {
            // Routed OK
        } elseif ($tipObject instanceof SOne_Interface_Object_WithSubRoute) {
            /** @var $tipObject SOne_Interface_Object_WithSubRoute */
            $subPath = preg_replace('#'.preg_quote(trim($tipObject->path, '/').'/', '#').'#i', '', $request->path);
            $tipObject = $tipObject->routeSubPath($subPath, $request, $this->_env);
        } else {
            $tipObject = new SOne_Model_Object_Page404(array('path' => $request->path));
        }

        if ($tipObject->accessLevel > $this->_env->get('user')->accessLevel) {
            $tipObject = new SOne_Model_Object_Page403(array('path' => $request->path));
        }

        // performing action
        if ($performAction && $request->action) {
            if ($tipObject->isActionAllowed($request->action, $this->_env->get('user'))) {
                $tipObject->doAction($request->action, $this->_env, $objectUpdated);
                // TODO: think about deleting
                // NOTE: static objects are not for save
                if ($objectUpdated && !$tipObject->isStatic) {
                    $this->_objects->save($tipObject);
                }
            } else {
                $tipObject = new SOne_Model_Object_Page403(array('path' => $request->path));
            }
        }

        return $tipObject;
    }

    protected function renderPage(SOne_Model_Object $pageObject)
    {
        $pageNode = new FVISNode('GLOBAL_HTMLPAGE', 0, $this->_VIS);
        $this->_VIS->setRootNode($pageNode);

        $objectNode = $pageObject->visualize($this->_env);

        if ($this->_env->request->isAjax) {
            return $objectNode->parse();
        }

        $pageNode->appendChild('page_cont', $objectNode);
        $pageNode->addData('site_name', $this->_config->site->name);
        $pageNode->addData('responsive', $this->_config->markup->responsive ? 1 : null);
        $pageNode->addData('page_title', $pageObject->caption);
        //$pageNode->addData('page_cont', '<pre>'.print_r(get_included_files(), true).'</pre>');
        //$pageNode->addData('page_cont', '<pre>'.print_r($this->env, true).'</pre>');

        $widgets = $this->bootstrapWidgets();
        foreach ($widgets as $widgetId => $widget) {
            if ($widget instanceof SOne_Model_Widget) {
                /** @var $widget SOne_Model_Widget */
                if ($widget->block && $visNode = $widget->visualize($this->_env, $pageObject)) {
                    $widgetContainer = new FVISNode('SONE_WIDGET_CONTAINER', 0, $this->_VIS);
                    $widgetContainer->appendChild('body', $visNode)
                        ->addData('widgetId', $widgetId);
                    $pageNode->appendChild($widget->block.'_widgets', $widgetContainer);
                }
            }
        }

        $pageNode->appendChild('navigator', $this->renderDefaultNavigator($pageObject->path));

        $this->throwEvent(self::EVENT_PAGE_RENDER, $this->_VIS->getRootNode());

        F()->Timer->logEvent('App Page Construct complete');

        return $this->_VIS->makeHTML();
    }

    /**
     * @param string $currentPath
     * @return FVISNode
     */
    protected function renderDefaultNavigator($currentPath)
    {
        $tree = $this->_objects->loadObjectsTreeByPath($currentPath, true);
        // loading static routes
        if ($staticRoutes = $this->_config->staticRoutes) {
            $staticRoutes = $staticRoutes->toArray();
            foreach ($staticRoutes as $path => $data) {
                // nodes with no caption or hidden nodes is not included
                if (!is_array($data) || !isset($data['caption']) || (isset($data['hideInTree']) && $data['hideInTree'])) {
                    continue;
                }

                // sub nodes are hidden for now
                if (substr_count($path, '/')) {
                    continue;
                }

                $data['path']      = $path;
                $data['isStatic']  = true;
                $data['treeLevel'] = 1;
                $tree[] = SOne_Model_Object::construct($data);
            }
        }

        $container = new FVISNode('NAVIGATOR_BLOCK', 0, $this->_VIS);
        /** @var $parents FVISNode[] */
        $parents = array($container, $container);

        if (is_array($tree)) {
            foreach ($tree as $item) {
                if ($item->hideInTree || $item->accessLevel > $this->_env->get('user')->accessLevel || !$parents[$item->treeLevel]) {
                    $parents[$item->treeLevel+1] = null;
                    continue;
                }

                $node = new FVISNode('NAVIGATOR_ITEM', 0, $this->_VIS);
                $parentNode = $parents[$item->treeLevel];
                if ($isActive = (strpos(trim($currentPath, '/').'/', trim($item->path, '/').'/') === 0)) {
                    $parentNode->addData('isCurrent', null, true);
                }
                $node->addDataArray(array(
                    'href' => FStr::fullUrl(ltrim($item->path, '/')),
                    'caption' => $item->caption,
                    'shortCaption' => FStr::smartTrim($item->caption, 23 - $item->treeLevel),
                    'isCurrent' => $isActive ? 1 : null,
                ));
                $parentNode->appendChild('subs', $node);
                $parents[$item->treeLevel+1] = $node;
            }
        }

        return $container;
    }

    protected function bootstrapPlugins()
    {
        $pluginsDir = isset($this->_config->pluginsDir)
            ? $this->_config->pluginsDir
            : F_SITE_ROOT.DIRECTORY_SEPARATOR.self::DEFAULT_PLUGINS_SUBDIR;

        if ($this->_config->plugins instanceof Traversable) {
            foreach ($this->_config->plugins as $pluginName => $pluginConfig) {
                if (is_dir($pluginsDir.DIRECTORY_SEPARATOR.$pluginName)) {
                    F()->Autoloader->registerClassPath($pluginsDir.DIRECTORY_SEPARATOR.$pluginName, $pluginName);
                    $pluginBootstrapClass = ($pluginConfig instanceof K3_Config) && isset($pluginConfig->bootstrapClass)
                        ? $pluginConfig->bootstrapClass
                        : $pluginName.'_Bootstrap';
                    if (class_exists($pluginBootstrapClass, true)) {
                        $pluginBootstrapClass::bootstrap($this, ($pluginConfig instanceof K3_Config) ? $pluginConfig : new K3_Config((array) $pluginConfig));
                    }
                }
            }
        }
    }

    /**
     * @return SOne_Model_Widget[]
     */
    protected function bootstrapWidgets()
    {
        $widgets = array();

        if ($this->_config->widgets instanceof Traversable) {
            foreach ($this->_config->widgets as $widgetName => $widgetConfig) {
                $widgets[$widgetName] = SOne_Model_Widget::construct(($widgetConfig instanceof K3_Config) ? $widgetConfig->toArray() : (array) $widgetConfig);
            }
        }

        $this->throwEventRef(self::EVENT_WIDGETS_BOOTSTRAP, $widgets);

        return (array) $widgets;
    }

    protected function _parseConfigLines(array $lines)
    {
        $constants = get_defined_constants(false);
        $replaces = array();
        foreach ($constants as $name => $value) {
            $replaces['{'.$name.'}'] = $value;
        }

        foreach ($lines as &$line) {
            $line = strtr($line, $replaces);
        }

        return $lines;
    }

    protected function _bootstrapUser()
    {
        $user = null;
        if ($uid = $this->_env->session->get('userId')) {
            /* @var SOne_Repository_User $users */
            $users = SOne_Repository_User::getInstance($this->_db);
            if ($user = $users->loadOne(array('id' => (int) $uid, 'last_sid' => $this->_env->session->getSID()))) {
                $users->save($user->updateLastSeen($this->_env));
            } else {
                $this->_env->session->drop('userId');
            }
        }
        if (!$user) {
            $user = new SOne_Model_User(array(
                'last_ip' => $this->_env->client->IPInteger,
            ));
        }

        return $user;
    }

    public function setAuthUser(SOne_Model_User $user)
    {
        /* @var SOne_Repository_User $users */
        $users = SOne_Repository_User::getInstance($this->_db);

        $this->_env->session->open();
        $users->save($user->updateLastSeen($this->_env));
        $this->_env->session->set('userId', $user->id);
        $this->_env->put('user', $user);
    }

    public function dropAuthUser()
    {
        $this->_env->session->drop('userId');
        $this->_env->put('user', new SOne_Model_User());
    }

    /**
     * @return \K3_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * @return \FLNGData
     */
    public function getLang()
    {
        return $this->_lang;
    }
}

