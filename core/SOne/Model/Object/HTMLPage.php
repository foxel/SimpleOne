<?php

class SOne_Model_Object_HTMLPage extends SOne_Model_Object
{
    public function visualize(K3_Environment $env)
    {
        if ($this->actionState == 'save') {
            $env->response->sendRedirect($this->path);
        }
        $node = new FVISNode('SONE_OBJECT_HTMLPAGE', 0, $env->get('VIS'));
        $node->addDataArray($this->pool);
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
