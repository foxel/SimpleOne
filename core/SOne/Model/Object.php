<?php

abstract class SOne_Model_Object extends FBaseClass
{
    /**
     * @param  array $init
     * @return SOne_Model_Object
     */
    public static function construct(array $init)
    {
        if (!isset($init['class'])) {
            return null;
        }

        $className = __CLASS__.'_'.ucfirst($init['class']);

        if (class_exists($className, true)) {
            return new $className($init);
        }

        return new SOne_Model_Object_Common($init);
    }

    /**
     * @param  array $init
     */
    public function __construct(array $init = array())
    {
        $this->pool = array(
            'id'          => isset($init['id'])          ? (int) $init['id']           : null,
            'parentId'    => isset($init['parent_id'])   ? (int) $init['parent_id']    : null,
            'className'   => isset($init['class'])       ? (string) $init['class']     : lcfirst(strtr(get_class($this), array(__CLASS__ => ''))),
            'caption'     => isset($init['caption'])     ? (string) $init['caption']   : '',
            'ownerId'     => isset($init['owner_id'])    ? (int) $init['owner_id']     : null,
            'createTime'  => isset($init['create_time']) ? (int) $init['create_time']  : time(),
            'updateTime'  => isset($init['update_time']) ? (int) $init['update_time']  : time(),
            'accessLevel' => isset($init['acc_lvl'])     ? (int) $init['acc_lvl']      : 0,
            'editLevel'   => isset($init['edit_lvl'])    ? (int) $init['edit_lvl']     : 3,
            'path'        => isset($init['path'])        ? (string) $init['path']      : '',
            'pathHash'    => isset($init['path_hash'])   ? (string) $init['path_hash'] : md5(isset($init['path']) ? (string) $init['path'] : ''),
            'orderId'     => isset($init['order_id'])    ? (int) $init['order_id']     : 0,
            'data'        => isset($init['data'])        ? unserialize($init['data'])  : null,
            'actionState' => null,
        );

        if (isset($init['t_level'])) {
            $this->pool['treeLevel'] = (int) $init['t_level'];
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
            $path = '/'.FStr::cast($path, FStr::PATH);
            $this->pool['path'] = $path;
            $this->pool['pathHash'] = md5($path);
            $this->pool['parent_id'] = null; // object with null path can't have parent
        } else {
            $this->pool['path'] = $this->pool['pathHash'] = null;
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
     * @param  K3_Environment $env
     * @param  boolean &$objectUpdated
     */
    public function doAction($action, K3_Environment $env, &$objectUpdated = false)
    {
        $this->pool['actionState'] = $action;
    }

    /**
     * @param  K3_Environment $env
     * @return FVISNode
     */
    abstract public function visualize(K3_Environment $env);
}
