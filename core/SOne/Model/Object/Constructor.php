<?php
/**
 * @property SOne_Model_Object $object
 * @author foxel
 */
class SOne_Model_Object_Constructor extends SOne_Model_Object implements SOne_Interface_Object_WithSubRoute
{
    /**
     * @param $action
     * @param K3_Environment $env
     * @param bool $objectUpdated
     */
    public function doAction($action, K3_Environment $env, &$objectUpdated = false)
    {
        parent::doAction($action, $env, $objectUpdated);

        if ($this->object) {
            $this->object->doAction($action, $env);
        }
    }

    protected function prepareAction(K3_Environment $env, &$objectUpdated = false)
    {
        $class = $env->request->getString('class', K3_Request::ALL, FStr::WORD);
        $path  = $env->request->getString('path',  K3_Request::ALL, FStr::WORD);
        $parentPath = $env->request->getString('parentPath', K3_Request::ALL, FStr::PATH);

        if (!$path) {
            $this->pool['errors'] = 'Путь является обязательным';
            return;
        }

        if ($parentObject = SOne_Repository_Object::getInstance($env->get('db'))->loadOne(array('pathHash' => md5($parentPath)))) {
            $path = FStr::cast('/'.$parentPath.'/'.$path, FStr::PATH);
            $uid = md5($this->path.$path);

            $object = SOne_Model_Object::construct(array(
                'class'       => $class,
                'path'        => $this->path.'/'.$uid,
                'parentId'    => $parentObject->id,
                'accessLevel' => $parentObject->accessLevel,
                'editLevel'   => $parentObject->editLevel,
                'ownerId'     => $env->get('user')->id,
            ));

            $env->session->set('constructor'.$uid, array($object, $path));

            $this->pool['object'] = $object;

            $this->pool['actionState'] = 'redirect';
        }
    }

    /**
     * @param  K3_Environment $env
     * @return FVISNode
     */
    public function visualize(K3_Environment $env)
    {
        $node = new FVISNode($this->actionState ? 'SONE_OBJECT_CONSTRUCTOR_FRAME' : 'SONE_OBJECT_CONSTRUCTOR', 0, $env->get('VIS'));
        if ($this->object) {
            if ($this->actionState == 'redirect') {
                return $env->response->sendRedirect($this->object->path.'?edit');
            } else {
                $node->appendChild('content', $this->object->visualize($env));
            }
        } else {
            $tree = SOne_Repository_Object::getInstance($env->get('db'))->loadObjectsTree();
            $pathOptions = array();
            foreach ($tree as $item) {
                if ($item instanceof SOne_Interface_Object_WithSubRoute) {
                    continue;
                }
                $pathOptions[] = array(
                    'path'    => $item->path,
                    'caption' => $item->caption,
                );
            }

            $node->appendChild('pathOptions', $optionsNode = new FVISNode('SONE_OBJECT_CONSTRUCTOR_PATHOPTION', FVISNode::VISNODE_ARRAY, $env->get('VIS')));
            $optionsNode->addDataArray($pathOptions);
        }

        $node->addDataArray(array(
            'path'        => $this->path,
            'caption'     => $this->caption,
            'errors'      => $this->errors,
            'hideCaption' => (bool) $env->request->isAjax,
        ));

        return $node;
    }

    /**
     * @param string $subPath
     * @param SOne_Request $request
     * @param K3_Environment $env
     * @return SOne_Model_Object
     */
    public function routeSubPath($subPath, SOne_Request $request, K3_Environment $env)
    {
        list($object, $objectPath) = $env->session->get('constructor'.$subPath);

        if ($object instanceof SOne_Model_Object) {
            $this->pool['object'] = $object;

            if ($request->action == 'save') {
                $this->object->path = $objectPath;
                return $this->object;
            }
        }

        return $this;
    }
}
