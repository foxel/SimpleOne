<?php
/**
 * Copyright (C) 2016 Andrey F. Kupreychik (Foxel)
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

/**
 * Class PushAll_Plugin
 */
class PushAll_Plugin
{
    /** @var SOne_Application */
    protected $_app;
    /** @var K3_Config */
    protected $_config;

    /**
     * @param SOne_Application $app
     * @param K3_Config $config
     */
    public function __construct(SOne_Application $app, K3_Config $config)
    {
        $this->_app    = $app;
        $this->_config = $config;

        if (!empty($this->_config->blogs)) {
            $this->_app->getObjects()->addEventHandler(SOne_Repository_Object::EVENT_OBJECT_CREATED, array($this, 'notifyBlogs'));
        }
    }

    /**
     * @param SOne_Model_Object $object
     */
    public function notifyBlogs(SOne_Model_Object $object)
    {
        if ($object instanceof SOne_Model_Object_BlogItem && $object->getPubDate() <= time()) {
            $parentPath = dirname($object->path);
            $options = $this->_config->blogs[$parentPath];

            if (($options instanceof K3_Config) && $options->channelId && $options->channelKey) {
                $ch = curl_init();
                curl_setopt_array($ch, array(
                    CURLOPT_URL => "https://pushall.ru/api.php",
                    CURLOPT_POSTFIELDS => array(
                        "type" => "broadcast",
                        "id" => $options->channelId,
                        "key" => $options->channelKey,
                        "text" => strip_tags($object->getDescription()),
                        "title" => $object->caption,
                        "url" => K3_Util_Url::fullUrl($object->path, $this->_app->getEnv()),
                    ),
                    CURLOPT_RETURNTRANSFER => true,
                ));
                $return=curl_exec($ch); //получить данные о рассылке
                curl_close($ch);
                // @TODO: log error if $return.success is not '1'
            }
        }
    }

    /**
     * @return K3_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }
}
