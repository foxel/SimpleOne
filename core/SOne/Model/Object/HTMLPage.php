<?php

class SOne_Model_Object_HTMLPage extends SOne_Model_Object_Commentable
{
    public function visualize(K3_Environment $env)
    {
        if (in_array($this->actionState, array('save', 'saveComment'))) {
            $env->response->sendRedirect($this->path);
        }
        $node = new FVISNode('SONE_OBJECT_HTMLPAGE', 0, $env->get('VIS'));
        $data = $this->pool;
        if ($data['comments']) {
            $node->appendChild('comments', $comments = new FVISNode('SONE_OBJECT_COMMENTS_ITEM', FVISNode::VISNODE_ARRAY, $env->get('VIS')));
            $comments->addDataArray($data['comments']);
            unset($data['comments']);
        }
        $node->addDataArray($data + array(
            'canEdit'       => $this->isActionAllowed('edit', $env->get('user')) ? 1 : null,
        ));
        return $node;
    }

    public function setData($data)
    {
        $this->pool['data'] = $data; // TODO: HTML parse
        return $this;
    }

    public function doAction($action, K3_Environment $env, &$updated = false)
    {
        parent::doAction($action, $env, $updated);
        if ($action == 'save') {
            $this->data = $env->request->getString('data', K3_Request::POST);
            $updated = true;
        }
    }
}
