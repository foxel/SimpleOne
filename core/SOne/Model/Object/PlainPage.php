<?php
/**
 * Copyright (C) 2012 - 2013 Andrey F. Kupreychik (Foxel)
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
abstract class SOne_Model_Object_PlainPage extends SOne_Model_Object_Commentable
    implements SOne_Interface_Object_Structured
{
    /**
     * @param SOne_Environment $env
     * @return FVISNode
     */
    public function visualize(SOne_Environment $env)
    {
        if (in_array($this->actionState, array('save', 'saveComment'))) {
            $env->response->sendRedirect($this->path);
        }
        $node = new FVISNode('SONE_OBJECT_PLAINPAGE', 0, $env->getVIS());
        $data = $this->pool;
        if ($this->commentsAllowed) {
            $node->appendChild('commentsBlock', $this->visualizeComments($env, (bool) $env->getUser()->id, 15));
        }
        unset($data['comments']);
        $node->addDataArray($data + array(
            'canEdit'       => $this->isActionAllowed('edit', $env->getUser()) ? 1 : null,
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
     * @param SOne_Environment $env
     * @param bool $updated
     */
    protected function saveAction(SOne_Environment $env, &$updated = false)
    {
        parent::saveAction($env, $updated);
        $this->content = $env->request->getString('content', K3_Request::POST);
        $this->pool['commentsAllowed'] = $env->request->getBinary('commentsAllowed', K3_Request::POST);
        $this->pool['updateTime'] = time();


        /* @var FLNGData $lang */
        $lang = $env->getLang();

        $errors = array();
        if (!$this->caption) {
            $errors[] = $lang->lang('SONE_OBJECT_ERROR_CAPTION_REQUIRED');
        }
        if (!$this->content) {
            $errors[] = $lang->lang('SONE_OBJECT_ERROR_CONTENT_REQUIRED');
        }

        if (!empty($errors)) {
            $this->pool['actionState'] = 'edit';
            $this->pool['errors'] = '<ul><li>'.implode('</li><li>', $errors).'</li></ul>';
        } else {
            $updated = true;
        }
    }

    /**
     * @param array $data
     * @return SOne_Model_Object_HTMLPage
     */
    public function setData(array $data)
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
