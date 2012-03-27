<?php

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
 *
 * @property int|null $treeLevel
 */
abstract class SOne_Model_Object extends SOne_Model
{
    const DEFAULT_ACCESS_LEVEL = 0;
    const DEFAULT_EDIT_LEVEL = 0;

    /**
     * @var array
     */
    protected $aclEditActionsList = array('edit', 'save');

    /**
     * @var array
     */
    protected static $classNamespaces = array(__CLASS__);

    /**
     * adds new namespace for object classes lookup
     * @param  string $namespace
     */
    public static function addNamespace($namespace)
    {
        $namespace = (string) $namespace;
        if (!in_array($namespace, self::$classNamespaces)) {
            self::$classNamespaces[] = $namespace;
        }
    }

    /**
     * @param  array $init
     * @return SOne_Model_Object
     */
    public static function construct(array $init)
    {
        if (!isset($init['class'])) {
            throw new FException('SOne object construct without class specified');
        }

        foreach (self::$classNamespaces as &$namespace) {
            $className = $namespace.'_'.ucfirst($init['class']);

            if (class_exists($className, true)) {
                return new $className($init);
            }
        }

        return new SOne_Model_Object_Common($init);
    }

    /**
     * @param  array $init
     */
    public function __construct(array $init = array())
    {
        $this->pool = array(
            'id'          => isset($init['id'])          ?  (int)    $init['id']          : null,
            'parentId'    => isset($init['parentId'])    ?  (int)    $init['parentId']    : null,
            'class'       => isset($init['class'])       ?  (string) $init['class']       : lcfirst(strtr(get_class($this), array(__CLASS__ => ''))),
            'caption'     => isset($init['caption'])     ?  (string) $init['caption']     : '',
            'ownerId'     => isset($init['ownerId'])     ?  (int)    $init['ownerId']     : null,
            'createTime'  => isset($init['createTime'])  ?  (int)    $init['createTime']  : time(),
            'updateTime'  => isset($init['updateTime'])  ?  (int)    $init['updateTime']  : time(),
            'accessLevel' => isset($init['accessLevel']) ?  (int)    $init['accessLevel'] : self::DEFAULT_ACCESS_LEVEL,
            'editLevel'   => isset($init['editLevel'])   ?  (int)    $init['editLevel']   : self::DEFAULT_EDIT_LEVEL,
            'orderId'     => isset($init['orderId'])     ?  (int)    $init['orderId']     : 0,

            'path'        => isset($init['path'])        ?  (string) $init['path']        : '',
            'pathHash'    => isset($init['pathHash'])    ?  (string) $init['pathHash']    : md5(isset($init['path']) ? (string) $init['path'] : ''),

            'data'        => isset($init['data'])        ? $init['data']                  : null,
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
            $path = FStr::cast(trim($path, '/'), FStr::PATH);
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
        if (in_array($action, $this->aclEditActionsList)) {
            return $this->pool['editLevel'] <= $user->accessLevel;
        }

        return $this->pool['accessLevel'] <= $user->accessLevel;
    }

    /**
     * This should be introduced in child classes
     * @param  string $action
     * @param  K3_Environment $env
     * @param  boolean &$objectUpdated
     */
    public function doAction($action, K3_Environment $env, &$objectUpdated = false)
    {
        $this->pool['actionState'] = $action;
        $actionMethod = $action.'Action';
        if (method_exists($this, $actionMethod)) {
            $this->$actionMethod($env, $objectUpdated);
        }
    }

    /**
     * saves caption
     * @param  K3_Environment $env
     * @param  boolean &$objectUpdated
     */
    protected function saveAction(K3_Environment $env, &$objectUpdated = false)
    {
        $newCaption = $env->request->getString('caption', K3_Request::POST, FStr::LINE);
        if ($newCaption) {
            $this->pool['caption'] = $newCaption;
            $objectUpdated = true;
        }
    }

    /**
     * @param  K3_Environment $env
     * @return FVISNode
     */
    abstract public function visualize(K3_Environment $env);
}
