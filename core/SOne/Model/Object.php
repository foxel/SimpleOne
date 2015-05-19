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
 * @property int    $id
 * @property int    $parentId
 * @property string $class
 * @property string $caption
 * @property int    $ownerId
 * @property int    $createTime
 * @property int    $updateTime
 * @property int    $accessLevel
 * @property int    $editLevel
 * @property int    $orderId
 *
 * @property string $path
 * @property string $pathHash
 *
 * @property mixed  $data
 * @property string $actionState
 * @property bool   $hideInTree
 *
 * @property bool   $isStatic
 *
 * @property int|null $treeLevel
 */
abstract class SOne_Model_Object extends SOne_Model_WithFactory implements I_K3_RSS_Item
{
    const DEFAULT_ACCESS_LEVEL = 0;
    const DEFAULT_EDIT_LEVEL   = 3;

    /** @var string */
    protected static $_defaultClass = 'SOne_Model_Object_Common';

    /** @var array */
    protected $aclEditActionsList = array('edit', 'save', 'delete');

    /**
     * @param  array $init
     */
    public function __construct(array $init = array())
    {
        $this->pool = array(
            'id'          => isset($init['id'])          ? (int)    $init['id']          : null,
            'parentId'    => isset($init['parentId'])    ? (int)    $init['parentId']    : null,
            'class'       => isset($init['class'])       ? (string) $init['class']       : lcfirst(strtr(get_class($this), self::_getClassReplaceMap(__CLASS__))),
            'caption'     => isset($init['caption'])     ? (string) $init['caption']     : '',
            'ownerId'     => isset($init['ownerId'])     ? (int)    $init['ownerId']     : null,
            'createTime'  => isset($init['createTime'])  ? (int)    $init['createTime']  : time(),
            'updateTime'  => isset($init['updateTime'])  ? (int)    $init['updateTime']  : time(),
            'accessLevel' => isset($init['accessLevel']) ? (int)    $init['accessLevel'] : self::DEFAULT_ACCESS_LEVEL,
            'editLevel'   => isset($init['editLevel'])   ? (int)    $init['editLevel']   : self::DEFAULT_EDIT_LEVEL,
            'orderId'     => isset($init['orderId'])     ? (int)    $init['orderId']     : 0,

            'path'        => isset($init['path'])        ? (string) $init['path']        : '',
            'pathHash'    => isset($init['pathHash'])    ? (string) $init['pathHash']    : md5(isset($init['path']) ? (string) $init['path'] : ''),
            'hideInTree'  => isset($init['hideInTree'])  ? (bool)   $init['hideInTree']  : false,

            'data'        => isset($init['data'])        ? $init['data']                 : null,
            'actionState' => null,

            'isStatic'    => isset($init['isStatic'])    ? (bool) $init['isStatic']      : false,
        );

        if (isset($init['treeLevel'])) {
            $this->pool['treeLevel'] = (int) $init['treeLevel'];
        }

        if (!isset($init['data']) && $data = array_diff_key($init, $this->pool)) {
            $this->pool['data'] = $data;
        }

        if ($this instanceof SOne_Interface_Object_Structured) {
            $this->setData((array)$this->pool['data']);
        }
    }

    /**
     * @param  integer|null $id
     * @return SOne_Model_Object
     */
    public function setId($id)
    {
        $this->pool['id'] = $id > 0 ? (int) $id : null;
        return $this;
    }

    /**
     * @param  integer|null $parentId
     * @return SOne_Model_Object
     */
    public function setParentId($parentId)
    {
        $this->pool['parentId'] = $parentId > 0 ? (int) $parentId : null;
        return $this;
    }

    /**
     * @param  string $path
     * @return SOne_Model_Object
     */
    public function setPath($path)
    {
        if (is_string($path)) {
            $path = K3_Util_String::filter(trim($path, '/'), K3_Util_String::FILTER_PATH);
            $this->pool['path'] = $path;
            $this->pool['pathHash'] = md5($path);
        } else {
            $this->pool['path'] = $this->pool['pathHash'] = null;
            $this->pool['parent_id'] = null; // object with null path can't have parent
        }
        return $this;
    }

    /**
     * @param  string $caption
     * @return SOne_Model_Object
     */
    public function setCaption($caption)
    {
        $this->pool['caption'] = (string) $caption;
        return $this;
    }

    /**
     * @param  integer|null $orderId
     * @return SOne_Model_Object
     */
    public function setOrderId($orderId)
    {
        $this->pool['orderId'] = (int) $orderId;
        return $this;
    }

    /**
     * @param  integer|null $userId
     * @return SOne_Model_Object
     */
    public function setOwnerId($userId)
    {
        $this->pool['ownerId'] = $userId > 0 ? (int) $userId : null;
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
     * This should be introduced in clild classes
     * @param  string $action
     * @param  SOne_Model_User $user
     * @return boolean
     */
    public function isActionAllowed($action, SOne_Model_User $user)
    {
        if (in_array($action, $this->aclEditActionsList) && (!$user->id || $user->id != $this->ownerId)) {
            return !$this->isStatic && $this->pool['editLevel'] <= $user->accessLevel;
        }

        return $this->pool['accessLevel'] <= $user->accessLevel;
    }

    /**
     * This should be introduced in child classes
     * @param  string $action
     * @param  SOne_Environment $env
     * @param  boolean &$objectUpdated
     */
    public function doAction($action, SOne_Environment $env, &$objectUpdated = false)
    {
        $this->pool['actionState'] = $action;
        $actionMethod = $action.'Action';
        // prevent cycle call
        if ($action != 'do' && method_exists($this, $actionMethod)) {
            $this->$actionMethod($env, $objectUpdated);
        }
    }

    /**
     * saves caption
     * @param  SOne_Environment $env
     * @param  boolean &$objectUpdated
     */
    protected function saveAction(SOne_Environment $env, &$objectUpdated = false)
    {
        $newCaption = $env->request->getString('caption', K3_Request::POST, K3_Util_String::FILTER_LINE);
        if ($newCaption) {
            $this->pool['caption'] = $newCaption;
            $this->pool['updateTime'] = time();
            $objectUpdated = true;
        }
    }

    /**
     * @param  SOne_Environment $env
     * @return FVISNode
     */
    abstract public function visualize(SOne_Environment $env);

    /**
     * @return array
     */
    public function __sleep()
    {
        return array('pool');
    }

    // RSS methods

    /**
     * @return null|string
     */
    public function getAuthor()
    {
        return null;
    }

    /**
     * @return null|string
     */
    public function getDescription()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getGUID()
    {
        // TODO: fix dependency injection
        return md5(K3_Util_Url::fullUrl('object~'.$this->id, F()->appEnv));
    }

    /**
     * @return string
     */
    public function getLink()
    {
        // TODO: fix dependency injection
        return K3_Util_Url::fullUrl($this->path, F()->appEnv);
    }

    /**
     * @return string
     */
    public function getPubDate()
    {
        return date('r', $this->createTime);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->caption;
    }

    /**
     * @return string[]
     */
    public function getCategories()
    {
        return array();
    }

    /**
     * @return I_K3_RSS_Item_Enclosure[]|null
     */
    public function getEnclosures()
    {
        return array();
    }
}
