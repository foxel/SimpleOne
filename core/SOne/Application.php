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

    /**
     * @var K3_Config
     */
    protected $config  = null;

    /**
     * @var FDataBase
     */
    protected $db      = null;

    /**
     * @var SOne_Request
     */
    protected $request = null;

    /**
     * @var FVISInterface
     */
    protected $VIS     = null;

    /**
     * Objects repository
     * @var SOne_Repository_Object
     */
    protected $objects = null; // objects repository

    /**
     * @var FLNGData
     */
    protected $lang    = null;

    public function __construct(K3_Environment $env = null)
    {
        $this->env = is_null($env) ? F()->appEnv : $env;

        $this->pool = array(
            'environment' => &$this->env,
            'config'      => &$this->config,
        );
    }

    public function bootstrap()
    {
        F()->Timer->logEvent('App Bootstrap start');

        $this->config = new K3_Config($c = (array) FMisc::loadDatafile(F_DATA_ROOT.DIRECTORY_SEPARATOR.'sone.qfc.php', FMisc::DF_SLINE));

        // preparing DB
        $this->db = F()->DBase; //new FDataBase('mysql');
        $this->db->connect($this->config->db);

        if ($this->config->app->useTransaction) {
            $this->db->beginTransaction();
            $this->getResponse()->addEventHandler('closeAndExit', array($this, 'commitOnResponseSent'));
        }

        $this->env->session->setDBase($this->db, 'sessions');

        $this->request = new SOne_Request($this->env, $this->config);

        $this->VIS = new FVISInterface($this->env);
        $this->VIS->addAutoLoadDir(F_DATA_ROOT.'/styles/simple')
            ->loadECSS(F_DATA_ROOT.'/styles/simple/common.ecss');
        F()->Parser->initStdTags();
        $this->VIS->addFuncParser('BBPARSE', array(F()->Parser, 'parse'));

        $this->objects = SOne_Repository_Object::getInstance($this->db);

        $this->lang = F()->LNG;
        $this->lang->addAutoLoadDir(F_DATA_ROOT.DIRECTORY_SEPARATOR.'lang/ru');
        $this->lang->timeZone = 7;

        // putting to environment
        $this->env
            ->put('db',   $this->db)
            ->put('VIS',  $this->VIS)
            ->put('user', $this->bootstrapUser())
            ->put('lang', $this->lang)
            ->put('app',  $this);

        $this->bootstrapPlugins();

        F()->Timer->logEvent('App Bootstrap end');

        return $this;
    }

    public function commitOnResponseSent()
    {
        if ($this->db->inTransaction) {
            $this->db->commit();
        }
    }

    public function run()
    {
        $object = $this->routeRequest($this->request, true);

        F()->Timer->logEvent('App Action end');

        $response = $this->renderPage($object);

        $this->getResponse()->clearBuffer()
            ->write($response)
            ->sendBuffer();
    }

    public function routeRequest(SOne_Request $request, $performAction = true)
    {
        $navis = $this->objects->loadNavigationByPath($request->path);
        $tipObject = !empty($navis) ? end($navis) : null;

        /** @var $tipObject SOne_Model_Object */
        $tipObject = $tipObject ? $this->objects->loadOne($tipObject['id']) : null;
        
        if (($tipObject instanceof SOne_Model_Object) && (trim($tipObject->path, '/') == $request->path)) {
            // Routed OK
        } elseif ($tipObject instanceof SOne_Interface_Object_WithSubRoute) {
            /** @var $tipObject SOne_Interface_Object_WithSubRoute */
            $subPath = preg_replace('#'.preg_quote(trim($tipObject->path, '/').'/', '#').'#i', '', $request->path);
            $tipObject = $tipObject->routeSubPath($subPath, $request, $this->env);
        } else {
            $tipObject = new SOne_Model_Object_Page404(array('path' => $request->path));
        }

        if ($tipObject->accessLevel > $this->env->get('user')->accessLevel) {
            $tipObject = new SOne_Model_Object_Page403(array('path' => $request->path));
        }

        // performing action
        if ($performAction && $request->action) {
            if ($tipObject->isActionAllowed($request->action, $this->env->get('user'))) {
                $tipObject->doAction($request->action, $this->env, $objectUpdated);
                // TODO: think about deleting
                if ($objectUpdated) {
                    $this->objects->save($tipObject);
                }
            } else {
                $tipObject = new SOne_Model_Object_Page403(array('path' => $request->path));
            }
        }

        return $tipObject;
    }

    protected function renderPage(SOne_Model_Object $pageObject)
    {
        $pageNode = new FVISNode('GLOBAL_HTMLPAGE', 0, $this->VIS);
        $this->VIS->setRootNode($pageNode);

        $objectNode = $pageObject->visualize($this->env);

        if ($this->env->request->isAjax) {
            return $objectNode->parse();
        }

        $pageNode->appendChild('page_cont', $objectNode);
        $pageNode->addData('site_name', $this->config->site->name);
        $pageNode->addData('page_title', $pageObject->caption);
        //$pageNode->addData('page_cont', '<pre>'.print_r(get_included_files(), true).'</pre>');
        //$pageNode->addData('page_cont', '<pre>'.print_r($this->env, true).'</pre>');

        $pageNode->appendChild('navigator', $this->renderDefaultNavigator($this->objects->loadObjectsTreeByPath($pageObject->path, true), $pageObject->path));

        F()->Timer->logEvent('App Page Construct complete');

        return $this->VIS->makeHTML();
    }

    /**
     * @param SOne_Model_Object[] $tree
     * @param string $currentPath
     * @return FVISNode
     */
    protected function renderDefaultNavigator($tree, $currentPath)
    {
        $container = new FVISNode('NAVIGATOR_BLOCK', 0, $this->VIS);
        $parents = array($container, $container);

        if (is_array($tree)) {
            foreach ($tree as $item) {
                if ($item->accessLevel > $this->env->get('user')->accessLevel || !$parents[$item->treeLevel]) {
                    $parents[$item->treeLevel+1] = null;
                    continue;
                }

                $node = new FVISNode('NAVIGATOR_ITEM', 0, $this->VIS);
                $node->addDataArray(array(
                    'href' => FStr::fullUrl(ltrim($item->path, '/')),
                    'caption' => $item->caption,
                    'shortCaption' => FStr::smartTrim($item->caption, 23 - $item->treeLevel),
                    'isCurrent' => (trim($item->path, '/') == trim($currentPath, '/')) ? 1 : null,
                ));
                $parents[$item->treeLevel]->appendChild('subs', $node);
                $parents[$item->treeLevel+1] = $node;
            }
        }

        return $container;
    }

    protected function bootstrapPlugins()
    {
        $pluginsDir = isset($this->config->pluginsDir)
            ? $this->config->pluginsDir
            : F_SITE_ROOT.DIRECTORY_SEPARATOR.self::DEFAULT_PLUGINS_SUBDIR;

        if ($this->config->plugins instanceof Traversable) {
            foreach ($this->config->plugins as $pluginName => $pluginConfig) {
                if (is_dir($pluginsDir.DIRECTORY_SEPARATOR.$pluginName)) {
                    F()->Autoloader->registerClassPath($pluginsDir.DIRECTORY_SEPARATOR.$pluginName, $pluginName);
                    $pluginBootstrapClass = ($pluginConfig instanceof K3_Config) && isset($pluginConfig->bootstrapClass)
                        ? $pluginConfig->bootstrapClass
                        : $pluginName.'_Bootstrap';
                    if (class_exists($pluginBootstrapClass, true)) {
                        $pluginBootstrapClass::bootstrap($this, $pluginConfig);
                    }
                }
            }
        }
    }

    protected function bootstrapUser()
    {
        $user = null;
        if ($uid = $this->env->session->get('userId')) {
            /* @var SOne_Repository_User $users */
            $users = SOne_Repository_User::getInstance($this->db);
            if ($user = $users->loadOne(array('id' => (int) $uid, 'last_sid' => $this->env->session->getSID()))) {
                $users->save($user->updateLastSeen($this->env));
            } else {
                $this->env->session->drop('userId');
            }
        }
        if (!$user) {
            $user = new SOne_Model_User(array(
                'last_ip' => $this->env->client->IPInteger,
            ));
        }

        return $user;
    }

    public function setAuthUser(SOne_Model_User $user)
    {
        /* @var SOne_Repository_User $users */
        $users = SOne_Repository_User::getInstance($this->db);

        $this->env->session->open();
        $users->save($user->updateLastSeen($this->env));
        $this->env->session->set('userId', $user->id);
        $this->env->put('user', $user);
    }

    public function dropAuthUser()
    {
        $this->env->session->drop('userId');
        $this->env->put('user', new SOne_Model_User());
    }
}

