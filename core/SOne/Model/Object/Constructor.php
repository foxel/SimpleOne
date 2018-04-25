<?php
/**
 * Copyright (C) 2012 - 2013, 2018 Andrey F. Kupreychik (Foxel)
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
 * @property SOne_Model_Object $object
 * @author foxel
 */
class SOne_Model_Object_Constructor extends SOne_Model_Object implements SOne_Interface_Object_WithSubRoute
{
    /**
     * @param $action
     * @param SOne_Environment $env
     * @param bool $objectUpdated
     */
    public function doAction($action, SOne_Environment $env, &$objectUpdated = false)
    {
        parent::doAction($action, $env, $objectUpdated);

        if ($this->object) {
            $this->object->doAction($action, $env);
        }
    }

    /**
     * @param string $action
     * @param SOne_Model_User $user
     * @return bool
     */
    public function isActionAllowed($action, SOne_Model_User $user)
    {
        if ($this->object) {
            return $this->object->isActionAllowed($action, $user);
        }

        return parent::isActionAllowed($action, $user);
    }


    /**
     * @param SOne_Environment $env
     * @param bool $objectUpdated
     * @return void
     */
    protected function prepareAction(SOne_Environment $env, &$objectUpdated = false)
    {
        $objectUpdated = false;

        $class = $env->request->getString('class', K3_Request::ALL, K3_Util_String::FILTER_WORD);
        $path  = $env->request->getString('path',  K3_Request::ALL, K3_Util_String::FILTER_WORD);
        $parentPath = $env->request->getString('parentPath', K3_Request::ALL, K3_Util_String::FILTER_PATH);
        $accessLevel = $env->request->getNumber('accessLevel', K3_Request::ALL);
        $editLevel   = $env->request->getNumber('editLevel', K3_Request::ALL);

        if (!$path) {
            $this->pool['errors'] = 'Путь является обязательным';
            return;
        }

        $parentObject = null;
        if ($parentPath) {
            $path = K3_Util_String::filter($parentPath.'/'.$path, K3_Util_String::FILTER_PATH);
            $parentObject = SOne_Repository_Object::getInstance($env->getDb())->loadOne(array('pathHash' => md5($parentPath)));
            if (!$parentObject) {
                $this->pool['errors'] = 'Невозможно загрузить указанный родительсвий объект';
                return;
            }
            $collidedObject = SOne_Repository_Object::getInstance($env->getDb())->loadOne(array('pathHash' => md5($path)));
            if ($collidedObject) {
                $this->pool['errors'] = 'Объект с заданным путем уже существует';
                return;
            }
        }

        $uid = md5($this->path.$path);

        if (is_null($accessLevel)) {
            $accessLevel = SOne_Model_Object::DEFAULT_ACCESS_LEVEL;
        } else {
            $accessLevel = min($accessLevel, $env->user->accessLevel);
        }

        if (is_null($editLevel) || !$env->user->modLevel) {
            $editLevel = SOne_Model_Object::DEFAULT_EDIT_LEVEL;
        } else {
            $editLevel = min($editLevel, $env->user->modLevel);
        }

        $editLevel = max($accessLevel, $editLevel);

        $object = SOne_Model_Object::construct(array(
            'class'       => $class,
            'path'        => $this->path.'/'.$uid,
            'parentId'    => $parentObject ? $parentObject->id : null,
            'accessLevel' => $parentObject ? max($parentObject->accessLevel, $accessLevel) : $accessLevel,
            'editLevel'   => $parentObject ? max($parentObject->accessLevel, $editLevel)   : $editLevel,
            'ownerId'     => $env->getUser()->id,
        ));

        $env->session->set('constructor'.$uid, array($object, $path));

        $this->pool['object'] = $object;

        $this->pool['actionState'] = 'redirect';
    }

    /**
     * @param  SOne_Environment $env
     * @return FVISNode
     */
    public function visualize(SOne_Environment $env)
    {
        $node = new FVISNode($this->actionState ? 'SONE_OBJECT_CONSTRUCTOR_FRAME' : 'SONE_OBJECT_CONSTRUCTOR', 0, $env->getVIS());
        if ($this->object) {
            if ($this->actionState == 'redirect') {
                $env->response->sendRedirect($this->object->path.'?edit');
            } else {
                $node->appendChild('content', $this->object->visualize($env));
            }
        } else {
            $repo = SOne_Repository_Object::getInstance($env->getDb());
            $allClasses = $repo->loadClasses(array());
            $supportedClasses = array();
            foreach ($allClasses as $classRef) {
                $className = SOne_Model_Object::resolveClass($classRef);
                if (in_array('SOne_Interface_Object_AcceptChildren', class_implements($className))) {
                    $supportedClasses[] = $classRef;
                }
            }

            $pathOptions = array();
            if (!empty($supportedClasses)) {
                $tree = SOne_Repository_Object::getInstance($env->getDb())->loadObjectsTree(array(
                    'class' => $supportedClasses,
                ));
                foreach ($tree as $item) {
                    // just in case
                    if (!$item instanceof SOne_Interface_Object_AcceptChildren) {
                        continue;
                    }
                    /** @var SOne_Model_Object $item */
                    $pathOptions[] = array(
                        'path'    => $item->path,
                        'caption' => $item->caption,
                    );
                }
            }

            $node->addDataArray($env->user->toArray(), 'user_');
            $node->addDataArray(array(
                'defaultAccessLevel' => SOne_Model_Object::DEFAULT_ACCESS_LEVEL,
                'defaultEditLevel' => SOne_Model_Object::DEFAULT_EDIT_LEVEL,
            ));

            if ($pathOptions) {
                $node->appendChild('pathOptions', $optionsNode = new FVISNode('SONE_OBJECT_CONSTRUCTOR_PATHOPTION', FVISNode::VISNODE_ARRAY, $env->getVIS()));
                $optionsNode->addDataArray($pathOptions);
            }
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
     * @param SOne_Environment $env
     * @return SOne_Model_Object
     */
    public function routeSubPath($subPath, SOne_Request $request, SOne_Environment $env)
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
