<?php
/**
 * Copyright (C) 2012 Andrey F. Kupreychik (Foxel)
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
 * @property string $content
 */
class SOne_Model_Object_HTMLPage extends SOne_Model_Object_Commentable
{
    /**
     * @param  array $init
     */
    public function __construct(array $init = array())
    {
        parent::__construct($init);

        $this->setData((array) $this->pool['data']);
    }

    /**
     * @param K3_Environment $env
     * @return FVISNode
     */
    public function visualize(K3_Environment $env)
    {
        if (in_array($this->actionState, array('save', 'saveComment'))) {
            $env->response->sendRedirect($this->path);
        }
        $node = new FVISNode('SONE_OBJECT_HTMLPAGE', 0, $env->get('VIS'));
        $data = $this->pool;
        if ($this->commentsAllowed) {
            $node->appendChild('commentsBlock', $this->visualizeComments($env, (bool) $env->get('user')->id, 15));
        }
        unset($data['comments']);
        $node->addDataArray($data + array(
            'canEdit'       => $this->isActionAllowed('edit', $env->get('user')) ? 1 : null,
        ));
        return $node;
    }

    /**
     * @param string $content
     * @return SOne_Model_Object_HTMLPage
     */
    public function setContent($content)
    {
        $this->pool['content'] = $content; // TODO: HTML parse
        return $this;
    }

    /**
     * This should be introduced in clild classes
     * @param  string $action
     * @param  SOne_Model_User $user
     * @return boolean
     */
    public function isActionAllowed($action, SOne_Model_User $user)
    {
        $allowed = parent::isActionAllowed($action, $user);

        if ($action == 'addComment' && !$user->id) {
            $allowed = false;
        }

        return $allowed;
    }

    /**
     * @param K3_Environment $env
     * @param bool $updated
     */
    protected function saveAction(K3_Environment $env, &$updated = false)
    {
        parent::saveAction($env, $updated);
        $this->content = $env->request->getString('content', K3_Request::POST);
        $this->pool['commentsAllowed'] = $env->request->getBinary('commentsAllowed', K3_Request::POST);
        $this->pool['updateTime'] = time();
        $updated = true;
    }

    /**
     * @param array $data
     * @return SOne_Model_Object_HTMLPage
     */
    protected function setData(array $data)
    {
        $this->pool['data'] = $data + array(
            'commentsAllowed' => false,
            'content'         => '',
        );
        $this->pool['commentsAllowed'] =& $this->pool['data']['commentsAllowed'];
        $this->pool['content']         =& $this->pool['data']['content'];
        return $this;
    }
}
