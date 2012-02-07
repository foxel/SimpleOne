<?php

class SOne_Application extends K3_Application
{
    protected $config  = array();
    protected $db      = null;
    protected $request = null;
    protected $VIS     = null;
    protected $objects = null; // objects repository

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
        $this->config = FMisc::loadDatafile(F_DATA_ROOT.DIRECTORY_SEPARATOR.'sone.qfc.php', FMisc::DF_SLINE);

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

        $this->env->session->setDBase($this->db, 'sessions');

        $this->request = new SOne_Request($this->env);
        $this->VIS = new FVISInterface($this->env);
        $this->VIS->addAutoLoadDir(F_DATA_ROOT.'/styles/simple')
            ->loadECSS(F_DATA_ROOT.'/styles/simple/common.ecss');

        $this->objects = new SOne_Repository_Object($this->db);

        // putting to environment
        $this->env
            ->put('db',   $this->db)
            ->put('VIS',  $this->VIS)
            ->put('user', $this->bootstrapUser())
            ->put('app',  $this);

        return $this;
    }

    public function run()
    {
        $navis = $this->objects->loadNavigationByPath($this->request->path);
        $tipObject = end($navis);
        if (trim($tipObject['path'], '/') == $this->request->path) {
            $tipObject = $this->objects->loadOne($tipObject['id']);
            // performing action
            if ($this->request->action) {
                $tipObject->doAction($this->request->action, $this->env, $objectUpdated);
                // TODO: think about deleting
                if ($objectUpdated) {
                    $this->objects->save($tipObject);
                }
            }
        } else {
            $tipObject = new SOne_Model_Object_Page404(array('path' => $this->request->path));
            $this->getResponse()->setStatusCode(404);
        }

        $this->renderPage($tipObject);
    }

    protected function renderPage(SOne_Model_Object $pageObject)
    {
        $pageNode = new FVISNode('GLOBAL_HTMLPAGE', 0, $this->VIS);
        $this->VIS->setRootNode($pageNode);

        $objectNode = $pageObject->visualize($this->env);

        if ($this->env->request->isAjax) {
            $this->getResponse()->clearBuffer()
                ->write($objectNode->parse())
                ->sendBuffer();
        }

        $pageNode->appendChild('page_cont', $objectNode);

        $pageNode->appendChild('navigator', $this->renderDefaultNavigator($this->objects->loadObjectsTreeByPath($pageObject->path, true)));

        $this->getResponse()->clearBuffer()
            ->write($this->VIS->makeHTML())
            ->sendBuffer();
    }

    protected function renderDefaultNavigator($tree)
    {
        $container = new FVISNode('NAVIGATOR_BLOCK', 0, $this->VIS);
        $parents = Array($container, $container);

        foreach ($tree as $item) {
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
            $users = new SOne_Repository_User($this->db);
            $user = $users->loadOne((int) $uid);
        }
        if (!$user) {
            $user = new SOne_Model_User(array(
                'last_ip' => $this->env->clientIPInteger,
            ));
        }

        return $user;
    }
}
