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
 * @property-read int $channelId
 */
class PushAll_Model_Widget_PushAllSubscribe extends SOne_Model_Widget
{
    /**
     * @param array $init
     */
    public function __construct(array $init = array())
    {
        parent::__construct($init);

        $this->setData((array)$this->pool['data']);
    }

    /**
     * @param SOne_Environment $env
     * @param SOne_Model_Object $pageObject
     * @return FVISNode
     */
    public function visualize(SOne_Environment $env, SOne_Model_Object $pageObject = null)
    {
        $node = new FVISNode('SONE_WIDGET_PUSHALL_SUBSCRIBE', 0, $env->getVIS());
        $node->addDataArray($this->pool);

        return $node;
    }

    /**
     * @param array $data
     * @return static
     */
    protected function setData(array $data)
    {
        $this->pool['data'] = $data + array(
            'channelId' => null,
        );
        $this->pool['channelId'] =& $this->pool['data']['channelId'];
        return $this;
    }
}
