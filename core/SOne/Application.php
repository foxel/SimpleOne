<?php

class SOne_Application extends K3_Application
{
    protected $config  = array();
    protected $db      = null;
    protected $request = null;
    protected $VIS     = null;
    protected $objects = null; // objects repository
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

        $this->config = new FDataPool($c = (array) FMisc::loadDatafile(F_DATA_ROOT.DIRECTORY_SEPARATOR.'sone.qfc.php', FMisc::DF_SLINE));

        // preparing DB
        $this->db = F()->DBase; //new FDataBase('mysql');
        $this->db->connect(
            array(
                'dbname' => $this->config['db.database'],
                'host'   => $this->config['db.host'],
            ),
            $this->config['db.username'],
            $this->config['db.password'],
            $this->config['db.prefix']
        );

        if ($this->config['app.useTransaction']) {
            $this->db->beginTransaction();
        }

        $this->env->session->setDBase($this->db, 'sessions');

        $this->request = new SOne_Request($this->env);

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

        F()->Timer->logEvent('App Bootstrap end');

        return $this;
    }

    public function run()
    {
        $object = $this->routeRequest($this->request, true);

        F()->Timer->logEvent('App Action end');

        $response = $this->renderPage($object);

        if ($this->db->inTransaction) {
            $this->db->commit();
        }

        $this->getResponse()->clearBuffer()
            ->write($response)
            ->sendBuffer();
    }

    public function routeRequest(SOne_Request $request, $performAction = true)
    {
        $navis = $this->objects->loadNavigationByPath($request->path);
        $tipObject = end($navis);

        if (trim($tipObject['path'], '/') == $request->path) {
            $tipObject = $this->objects->loadOne($tipObject['id']);
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
        } else {
            $tipObject = new SOne_Model_Object_Page404(array('path' => $request->path));
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
        $pageNode->addData('site_name', $this->config['site.name']);
        $pageNode->addData('page_title', $pageObject->caption);
        //$pageNode->addData('page_cont', '<pre>'.print_r(get_included_files(), true).'</pre>');
        //$pageNode->addData('page_cont', '<pre>'.print_r($this->db->history, true).'</pre>');

        $pageNode->appendChild('navigator', $this->renderDefaultNavigator($this->objects->loadObjectsTreeByPath($pageObject->path, true)));

        F()->Timer->logEvent('App Page Construct complete');

        return $this->VIS->makeHTML();
    }

    protected function renderDefaultNavigator($tree)
    {
        $container = new FVISNode('NAVIGATOR_BLOCK', 0, $this->VIS);
        $parents = Array($container, $container);

        foreach ($tree as $item) {
            if ($item->accessLevel > $this->env->get('user')->accessLevel || !$parents[$item->treeLevel]) {
                $parents[$item->treeLevel+1] = null;
                continue;
            }

            $node = new FVISNode('NAVIGATOR_ITEM', 0, $this->VIS);
            $node->addDataArray(array(
                'href' => FStr::fullUrl($item->path),
                'scaption' => FStr::smartTrim($item->caption, 23 - $item->treeLevel),
                'isCurrent' => (trim($item->path, '/') == $this->request->path) ? 1 : null,
            ));
            $parents[$item->treeLevel]->appendChild('subs', $node);
            $parents[$item->treeLevel+1] = $node;
        }

        return $container;
    }


    protected function bootstrapUser()
    {
        $user = null;
        if ($uid = $this->env->session->userId) {
            $users = SOne_Repository_User::getInstance($this->db);
            if ($user = $users->loadOne(array('id' => (int) $uid, 'last_sid' => $this->env->session->getSID()))) {
                $users->save($user->updateLastSeen($this->env));
            } else {
                $this->env->session->drop('userId');
            }
        }
        if (!$user) {
            $user = new SOne_Model_User(array(
                'last_ip' => $this->env->clientIPInteger,
            ));
        }

        return $user;
    }

    public function setAuthUser(SOne_Model_User $user)
    {
        $users = SOne_Repository_User::getInstance($this->db);
        $this->env->session->open();
        $users->save($user->updateLastSeen($this->env));
        $this->env->session->userId = $user->id;
        $this->env->put('user', $user);
    }

    public function dropAuthUser()
    {
        $this->env->session->drop('userId');
        $this->env->put('user', new SOne_Model_User());
    }
}
