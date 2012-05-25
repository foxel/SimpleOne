<?php

class VK_Bootstrap implements SOne_Interface_PluginBootstrap
{

    public static function bootstrap(SOne_Application $app, $config)
    {
        SOne_Model_Object::addNamespace('VK_Model_Object');
    }
}