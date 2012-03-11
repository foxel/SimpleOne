<?php

class SOne_Model_Object_Page404 extends SOne_Model_Object
{
    public function visualize(K3_Environment $env)
    {
        $env->getResponse()->setStatusCode(404);
        return new FVISNode('SONE_PAGE_404', 0, $env->get('VIS'));
    }
}

