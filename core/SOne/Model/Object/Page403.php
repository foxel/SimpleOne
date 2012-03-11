<?php

class SOne_Model_Object_Page403 extends SOne_Model_Object
{
    public function visualize(K3_Environment $env)
    {
        $env->getResponse()->setStatusCode(403);
        return new FVISNode('SONE_PAGE_403', 0, $env->get('VIS'));
    }
}

