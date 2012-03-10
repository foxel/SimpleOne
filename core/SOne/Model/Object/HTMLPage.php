<?php

class SOne_Model_Object_HTMLPage extends SOne_Model_Object_Commentable
{
    /**
     * @param  array $init
     */
    public function __construct(array $init = array())
    {
        parent::__construct($init);

        $this->setData((array) $this->pool['data']);
    }

    public function visualize(K3_Environment $env)
    {
        if (in_array($this->actionState, array('save', 'saveComment'))) {
            $env->response->sendRedirect($this->path);
        }
        $node = new FVISNode('SONE_OBJECT_HTMLPAGE', 0, $env->get('VIS'));
        $data = $this->pool;
        if ($this->commentsAllowed) {
            $node->appendChild('commentsBlock', $this->visualizeComments($env, (bool) $env->get('user')->id, 15));
        }
        unset($data['comments']);
        $node->addDataArray($data + array(
            'canEdit'       => $this->isActionAllowed('edit', $env->get('user')) ? 1 : null,
        ));
        return $node;
    }

    public function setContent($content)
    {
        $this->pool['content'] = $content; // TODO: HTML parse
        return $this;
    }

    /**
     * This should be introduced in clild classes
     * @param  string $action
     * @param  SOne_Model_User $user
     * @return boolean
     */
    public function isActionAllowed($action, SOne_Model_User $user)
    {
        $allowed = parent::isActionAllowed($action, $user);

        if ($action == 'addComment' && !$user->id) {
            $allowed = false;
        }

        return $allowed;
    }

    protected function saveAction(K3_Environment $env, &$updated = false)
    {
        parent::saveAction($env, $updated);
        $this->content = $env->request->getString('content', K3_Request::POST);
        $this->pool['commentsAllowed'] = $env->request->getBinary('commentsAllowed', K3_Request::POST);
        $this->pool['updateTime'] = time();
        $updated = true;
    }

    protected function setData(array $data)
    {
        $this->pool['data'] = $data + array(
            'commentsAllowed' => false,
            'content'         => '',
        );
        $this->pool['commentsAllowed'] =& $this->pool['data']['commentsAllowed'];
        $this->pool['content']         =& $this->pool['data']['content'];
        return $this;
    }
}
