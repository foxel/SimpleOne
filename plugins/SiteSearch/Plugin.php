<?php
/**
 * Copyright (C) 2015 Andrey F. Kupreychik (Foxel)
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
 * Class SiteSearch_Plugin
 */
class SiteSearch_Plugin
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
        if (!empty($this->_config->server->host)) {
            $this->_app->getObjects()->addEventHandler(SOne_Repository_Object::EVENT_OBJECT_SAVED, array($this, 'updateIndex'));
        }
    }

    /**
     * @param SOne_Model_Object $object
     */
    public function updateIndex(SOne_Model_Object $object)
    {
        $data = array(
            'class'   => $object->class,
            'path'    => $object->path,
            'caption' => $object->caption,
        );

        if ($object instanceof SOne_Model_Object_PlainPage) {
            $data['content'] = $object->content;
            if ($object instanceof SOne_Model_Object_BlogItem) {
                $data['tags'] = $object->tags;
            }
        }

        $payload = json_encode($data, JSON_UNESCAPED_UNICODE);

        $ch = curl_init();

        $indexName = $this->_config->indexName ?: 'simpleone';

        curl_setopt($ch, CURLOPT_URL, 'http://'.$this->_config->server->host.':9200/'.rawurlencode($indexName).'/object/'.$object->id);
        curl_setopt($ch, CURLOPT_USERAGENT, 'QuickFox SimpleOne');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: '.strlen($payload)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_exec($ch);
//        $chleadapierr = curl_errno($ch);
//        $chleaderrmsg = curl_error($ch);
        curl_close($ch);
    }

    /**
     * @param $query
     * @return string
     * @throws FException
     */
    public function search($query)
    {
        $post = array(
            'fields' => array('path', 'caption', 'content'),
            'query' => array('filtered' => array(
                'query' => array('multi_match' => array(
                    'query'  => $query,
                    'fields' => array('caption', 'content', 'tags'),
                )),
                'filter' => array('exists' => array(
                    'field' => 'content'
                )),
//                'filter' => array('term' => array(
//                    'class' => 'BlogItem',
//                    '_cache' => false
//                )),
            )),
            'highlight' => array(
                'fields' => array(
                    'caption' => (object) array(),
                    'content' => (object) array(),
                ),
            ),
            'from' => 0,
            'size' => 10,
        );

        $payload = json_encode($post, JSON_UNESCAPED_UNICODE);

        $ch = curl_init();

        $indexName = $this->_config->indexName ?: 'simpleone';

        curl_setopt($ch, CURLOPT_URL, 'http://'.$this->_config->server->host.':9200/'.rawurlencode($indexName).'/object/_search');
        curl_setopt($ch, CURLOPT_USERAGENT, 'QuickFox SimpleOne');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: '.strlen($payload)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $result = curl_exec($ch);
        if ($result === false) {
            throw new FException(curl_error($ch), curl_errno($ch));
        }
        curl_close($ch);

        return $result;
    }

    /**
     * @return K3_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }
}
