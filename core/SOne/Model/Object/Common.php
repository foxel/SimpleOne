<?php

class SOne_Model_Object_Common extends SOne_Model_Object
{
    public function visualize(K3_Environment $env)
    {
        $node = new FVISNode('SONE_OBJECT_COMMON', 0, $env->get('VIS'));
        $node->addDataArray($this->pool);
        return $node;
    }
}
