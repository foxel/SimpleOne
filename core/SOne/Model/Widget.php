<?php
/**
 * Copyright (C) 2012 - 2013, 2015 Andrey F. Kupreychik (Foxel)
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
 * @property string $id
 * @property string $class
 * @property string $caption
 * @property string $block
 *
 * @property mixed  $data
 *
 * @property bool   $isStatic
 */
abstract class SOne_Model_Widget extends SOne_Model_WithFactory
{
    /** @var string */
    protected static $_defaultClass = 'SOne_Model_Widget_Panel';

    /**
     * @param  array $init
     */
    public function __construct(array $init = array())
    {
        $this->pool = array(
            'id'          => isset($init['id'])          ? (string) $init['id']          : K3_Util_String::shortUID(),
            'class'       => isset($init['class'])       ? (string) $init['class']       : lcfirst(strtr(get_class($this), self::_getClassReplaceMap(__CLASS__))),
            'caption'     => isset($init['caption'])     ? (string) $init['caption']     : '',
            'block'       => isset($init['block'])       ? (string) $init['block']       : '',

            'data'        => isset($init['data'])        ? $init['data']                 : null,

            'isStatic'    => isset($init['isStatic'])    ? (bool) $init['isStatic']      : false,
        );

        if (!isset($init['data']) && $data = array_diff_key($init, $this->pool)) {
            $this->pool['data'] = $data;
        }
    }

    /**
     * @param  string $block
     * @return static
     */
    public function setBlock($block)
    {
        if (is_string($block)) {
            $this->pool['block'] = $block;
        } else {
            $this->pool['block'] = null;
        }

        return $this;
    }

    /**
     * @param  string $caption
     * @return static
     */
    public function setCaption($caption)
    {
        $this->pool['caption'] = (string) $caption;
        return $this;
    }

    /**
     * @return string
     */
    public function serializeData()
    {
        return serialize($this->pool['data']);
    }

    /**
     * @param  SOne_Environment $env
     * @param SOne_Model_Object $pageObject
     * @return FVISNode|null
     */
    abstract public function visualize(SOne_Environment $env, SOne_Model_Object $pageObject = null);

    /**
     * @return array
     */
    public function __sleep()
    {
        return array('pool');
    }
}
