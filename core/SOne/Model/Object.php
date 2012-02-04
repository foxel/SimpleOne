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
            'className'   => isset($init['class'])       ? (string) $init['class']     : $this->className,
            'caption'     => isset($init['caption'])     ? (string) $init['caption']   : '',
            'ownerId'     => isset($init['owner_id'])    ? (int) $init['owner_id']     : null,
            'createTime'  => isset($init['create_time']) ? (int) $init['create_time']  : time(),
            'updateTime'  => isset($init['update_time']) ? (int) $init['update_time']  : time(),
            'accessLevel' => isset($init['acc_lvl'])     ? (int) $init['acc_lvl']      : 0,
            'editLevel'   => isset($init['edit_lvl'])    ? (int) $init['edit_lvl']     : 0,
            'path'        => isset($init['path'])        ? (string) $init['path']      : '',
            'pathHash'    => isset($init['path_hash'])   ? (string) $init['path_hash'] : md5(isset($init['path']) ? (string) $init['path'] : ''),
            'orderId'     => isset($init['order_id'])    ? (int) $init['order_id']     : 0,
            'data'        => isset($init['data'])        ? unserialize($init['data'])  : null,
        );

        if (isset($init['t_level'])) {
            $this->pool['treeLevel'] = (int) $init['t_level'];
        }
    }

    /**
     * @param  K3_Environment $env
     * @return FVISNode
     */
    abstract public function visualize(K3_Environment $env);
}
