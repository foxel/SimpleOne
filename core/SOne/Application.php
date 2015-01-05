<?php
/**
 * Copyright (C) 2012 - 2014 Andrey F. Kupreychik (Foxel)
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

/**
 * @property SOne_Environment $_env protected
 */
class SOne_Application extends K3_Application
{
    const DEFAULT_PLUGINS_SUBDIR = 'plugins';
    const EVENT_PAGE_RENDERED = 'pageRendered';
    const EVENT_PAGE_OBJECT_VISUALIZED = 'pageObjectVisualized';
    const EVENT_PAGE_OBJECT_ROUTED = 'pageObjectRouted';
    const EVENT_WIDGETS_BOOTSTRAPPED = 'widgetsBootstrapped';
    const EVENT_CRON_PROCESS = 'cronProcess';

    const COOKIE_AUTO_LOGIN  = 'ALID';

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

    /**
     * @var string[]
     */
    protected $_loadedPlugins = array();

    /**
     * @param K3_Environment $env
     */
    public function __construct(K3_Environment $env = null)
    {
        $this->_env = SOne_Environment::prepare($env);
        $this->_env->setApp($this);

        $this->pool = array(
            'environment' => &$this->_env,
            'config'      => &$this->_config,
        );
    }

    /**
     * @return $this
     */
    public function bootstrap()
    {
        F()->Profiler->logEvent('App Bootstrap start');

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

        $tools = SOne_Tools::getInstance($this->_env);
        $this->getResponse()
            ->addEventHandler('HTML_parse', array($tools, 'HTML_FullURLs'));

        F()->Parser->initStdTags();
        $this->_VIS->addFuncParser('BBPARSE', array(F()->Parser, 'parse'));

        $this->_objects = SOne_Repository_Object::getInstance($this->_db);

        $this->_lang = F()->LNG;
        $this->_lang->addAutoLoadDir(F_DATA_ROOT.DIRECTORY_SEPARATOR.'lang/ru');
        $this->_lang->timeZone = 7;

        // putting to environment
        $this->_env
            ->setDb($this->_db)
            ->setVIS($this->_VIS)
            ->setLang($this->_lang)
            ->setUser($this->_bootstrapUser());

        $this->_bootstrapPlugins();

        F()->Profiler->logEvent('App Bootstrap end');

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

        F()->Profiler->logEvent('Object routed');

        // performing action
        if ($action = $this->_request->action) {
            if ($object->isActionAllowed($action, $this->_env->getUser())) {
                $object->doAction($action, $this->_env, $objectUpdated);
                // TODO: think about deleting
                // NOTE: static objects are not for save
                if ($objectUpdated && !$object->isStatic) {
                    $this->_objects->save($object);
                }
            } else {
                $object = new SOne_Model_Object_Page403(array('path' => $this->_request->path));
            }

            F()->Profiler->logEvent('App Action end');
        }

        if ($this->_env->request->isAjax && $object instanceof SOne_Interface_Object_WithAjaxResponse) {
            $object->ajaxResponse($this->_env, $this->getResponse());
        } else {
            $response = $this->renderPage($object);
            $this->getResponse()
                ->clearBuffer()
                ->write($response);
        }

        $this->getResponse()->sendBuffer();
    }

    /**
     * @param SOne_Request $request
     * @return SOne_Model_Object
     */
    public function routeRequest(SOne_Request $request)
    {
        /** @var $tipObject SOne_Model_Object */
        $tipObject = null;
        if ($this->_env->user->id && ($systemRoute = $this->_config->systemRoute) && ($request->path == $systemRoute || strpos($request->path, $systemRoute.'/') === 0)) {
            $tipObject = SOne_Model_Object::construct(array(
                'isStatic' => true,
                'class'    => 'System_Root',
                'path'     => $systemRoute,
            ));
        } else if (($staticRoutes = $this->_config->staticRoutes) && $staticRoutes instanceof K3_Config) {
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
            $subPath = preg_replace('#'.preg_quote(trim($tipObject->path, '/').'/', '#').'#i', '', $request->path);
            /** @var $tipObject SOne_Interface_Object_WithSubRoute */
            $tipObject = $tipObject->routeSubPath($subPath, $request, $this->_env);
        } else {
            $tipObject = new SOne_Model_Object_Page404(array('path' => $request->path));
        }

        if ($tipObject->accessLevel > $this->_env->getUser()->accessLevel) {
            $tipObject = new SOne_Model_Object_Page403(array('path' => $request->path));
        }

        $this->throwEvent(self::EVENT_PAGE_OBJECT_ROUTED, $tipObject);

        return $tipObject;
    }

    /**
     * @param SOne_Model_Object $pageObject
     * @return string
     */
    protected function renderPage(SOne_Model_Object $pageObject)
    {
        $objectNode = $pageObject->visualize($this->_env);

        $this->throwEvent(self::EVENT_PAGE_OBJECT_VISUALIZED, $objectNode, $pageObject);

        if ($this->_env->request->isAjax) {
            return $objectNode->parse();
        }

        $pageNode = new FVISNode('GLOBAL_HTMLPAGE', 0, $this->_VIS);
        $this->_VIS->setRootNode($pageNode);

        $pageNode->appendChild('page_cont', $objectNode);
        $pageNode->addData('site_name', $this->_config->site->name);
        $pageNode->addData('site_build', $this->_config->site->build);
        if ($this->_config->markup instanceof K3_Config) {
            $pageNode->addDataArray($this->_config->markup->toArray());
        }
        $pageNode->addData('page_title', $pageObject->caption);
        //$pageNode->addData('page_cont', '<pre>'.print_r(get_included_files(), true).'</pre>');
        //$pageNode->addData('page_cont', '<pre>'.print_r($this->env, true).'</pre>');

        $widgets = $this->_bootstrapWidgets();
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

        $this->throwEvent(self::EVENT_PAGE_RENDERED, $this->_VIS->getRootNode());

        F()->Profiler->logEvent('App Page Construct complete');

        return $this->_VIS->makeHTML();
    }

    /**
     * @param string[] $plugins
     * @throws FException
     */
    public function requirePlugins(array $plugins)
    {
        $notLoadedPlugins = array_diff($plugins, $this->_loadedPlugins);

        if (!empty($notLoadedPlugins)) {
            throw new FException('Plugins required: '.implode(', ', $notLoadedPlugins));
        }
    }

    protected function _bootstrapPlugins()
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
                        try {
                            $pluginBootstrapClass::bootstrap($this, ($pluginConfig instanceof K3_Config) ? $pluginConfig : new K3_Config((array) $pluginConfig));
                        } catch (Exception $e) {
                            throw new FException(sprintf('Error loading plugin "%s": %s', $pluginName, $e->getMessage()), 0, $e);
                        }

                        $this->_loadedPlugins[] = $pluginName;
                    }
                }
            }
        }
    }

    /**
     * @return SOne_Model_Widget[]
     */
    protected function _bootstrapWidgets()
    {
        $widgets = array();

        if ($this->_config->widgets instanceof Traversable) {
            foreach ($this->_config->widgets as $widgetId => $widgetConfig) {
                $init = ($widgetConfig instanceof K3_Config) ? $widgetConfig->toArray() : (array)$widgetConfig;
                $init['id'] = $widgetId;
                $widgets[$widgetId] = SOne_Model_Widget::construct($init);
            }
        }

        $this->throwEventRef(self::EVENT_WIDGETS_BOOTSTRAPPED, $widgets);

        return (array) $widgets;
    }

    /**
     * @param array $lines
     * @return array
     */
    protected function _parseConfigLines(array $lines)
    {
        $constants = get_defined_constants(true);
        $replaces = array();
        foreach ($constants['user'] as $name => $value) {
            $replaces['{'.$name.'}'] = $value;
        }

        $lines = str_replace(array_keys($replaces), array_values($replaces), $lines);

        return $lines;
    }

    /**
     * @return SOne_Model_User
     */
    protected function _bootstrapUser()
    {
        $user = null;
        /* @var SOne_Repository_User $users */
        $users = SOne_Repository_User::getInstance($this->_db);

        if ($uid = $this->_env->session->get('userId')) {
            if ($user = $users->loadOne(array('id' => (int) $uid, 'last_sid' => $this->_env->session->getSID()))) {
                $users->save($user->updateLastSeen($this->_env));
            } else {
                $this->_env->session->drop('userId');
            }
        } elseif ($alId = $this->_env->client->getCookie(self::COOKIE_AUTO_LOGIN)) {
            /** @var SOne_Repository_User_AutoLogin $alRepo */
            $alRepo = SOne_Repository_User_AutoLogin::getInstance($this->_db);
            $alSignature = $this->_env->client->getSignature(2);

            if ($alData = $alRepo->loadOne(array('id' => $alId))) {
                if ($alData->lastUsed > (time() - SOne_Model_User_AutoLogin::LIFETIME)) {
                    if (!$alData->userId) {
                    } elseif ($alSignature && $alSignature != $alData->userSig) {
                        $alRepo->delete(array('id' => $alId));
                    } elseif ($user = $users->loadOne(array('id' => (int)$alData->userId))) {
                        $users->save($user->updateLastSeen($this->_env));
                        $alData->update();
                        $alRepo->save($alData);
                        $this->_env->client->setCookie(self::COOKIE_AUTO_LOGIN, $alData->id, time() + SOne_Model_User_AutoLogin::LIFETIME);
                    }
                } else {
                    $alRepo->delete(array('lastUsed<' => (time() - SOne_Model_User_AutoLogin::LIFETIME)));
                }
            }
        }

        if (!$user) {
            $this->_env->client->setCookie(self::COOKIE_AUTO_LOGIN);
            $user = new SOne_Model_User(array(
                'last_ip' => $this->_env->client->IPInteger,
            ));
        }

        return $user;
    }

    /**
     * @param SOne_Model_User $user
     * @param bool $setSession
     * @param bool $setAutoLogin
     */
    public function setAuthUser(SOne_Model_User $user, $setSession = true, $setAutoLogin = false)
    {
        /* @var SOne_Repository_User $users */
        $users = SOne_Repository_User::getInstance($this->_db);

        $this->_env->session->open();
        $users->save($user->updateLastSeen($this->_env));

        if ($setAutoLogin) {
            /** @var SOne_Repository_User_AutoLogin $alRepo */
            $alRepo = SOne_Repository_User_AutoLogin::getInstance($this->_db);

            $alData = new SOne_Model_User_AutoLogin(array(
                'userId'  => $user->id,
                'userSig' => $this->_env->client->getSignature(2),
            ));

            $alRepo->save($alData);
            if ($alData->id) {
                $this->_env->client->setCookie(self::COOKIE_AUTO_LOGIN, $alData->id, time() + SOne_Model_User_AutoLogin::LIFETIME);
            }
        }

        if ($setSession) {
            $this->_env->session->set('userId', $user->id);
        }

        $this->_env->setUser($user);
    }

    public function dropAuthUser()
    {
        $this->_env->session->drop('userId');
        $this->_env->setUser(new SOne_Model_User());
        $this->_env->client->setCookie(self::COOKIE_AUTO_LOGIN);
    }

    /**
     * @return SOne_Environment
     */
    public function getEnv()
    {
        return $this->_env;
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

    /**
     * @return \SOne_Repository_Object
     */
    public function getObjects()
    {
        return $this->_objects;
    }

    /**
     * @return \SOne_Request
     */
    public function getRequest()
    {
        return $this->_request;
    }


}

