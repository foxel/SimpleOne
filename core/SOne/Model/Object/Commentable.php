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
 * @property boolean $commentsAllowed
 * @property array   $comments
 */
abstract class SOne_Model_Object_Commentable extends SOne_Model_Object implements SOne_Interface_Object_WithExtraData
{
    public function __construct(array $init = array())
    {
        parent::__construct($init);
        $this->pool['commentsAllowed'] = true;

        $this->setComments(isset($init['comments']) ? $init['comments'] : array());
    }

    /**
     * This should be introduced in clild classes
     * @param  string $action
     * @param  K3_Environment $env
     * @param  boolean &$objectUpdated
     */
    public function doAction($action, K3_Environment $env, &$objectUpdated = false)
    {
        parent::doAction($action, $env, $objectUpdated);

        if (!$this->commentsAllowed) {
            return;
        }

        switch ($action) {
            case 'saveComment':
                $this->addComment(
                    $env->request->getString('commentText', K3_Request::POST),
                    $env->request->getNumber('commentAnswerTo', K3_Request::POST),
                    array(
                        'client_ip' => $env->clientIPInteger,
                        'author_id' => $env->get('user')->id,
                    )
                );
                $objectUpdated = true;
                break;
        }
    }

    public function addComment($text, $answerTo = null, array $params = array())
    {
        $tmpId = '_'.count($this->pool['comments']);

        $newComment = array(
            'id'        => $tmpId,
            'answer_to' => null,
            'time'      => isset($params['time']) ? (int) $params['time'] : time(),
            'client_ip' => isset($params['client_ip']) ? (int) $params['client_ip'] : null,
            'author_id' => isset($params['author_id']) ? (int) $params['author_id'] : null,
            'text'      => $text,
            't_level'   => 0,
        );

        if (!is_null($answerTo) && isset($this->pool['comments'][$answerTo])) {
            $newComment['answer_to'] = $answerTo;
            $newComment['t_level']   = $this->pool['comments'][$answerTo]['t_level'] + 1;

            $oldTree = $this->pool['comments'];
            $this->pool['comments'] = array();
            foreach ($oldTree as $key => &$comment) {
                $this->pool['comments'][$key] =& $comment;
                if ($key == $answerTo) {
                    $this->pool['comments'][$tmpId] =& $newComment;
                }
            }
        } else {
            $this->pool['comments'] = array($tmpId => $newComment) + $this->pool['comments'];
        }

        return $this;
    }

    public function setComments(array $comments)
    {
        $this->pool['comments'] = F2DArray::tree($comments, 'id', 'answer_to');
    }

    public function visualizeComments(K3_Environment $env, $allowAddComment = true, $commentsPerPage = false)
    {
        $node = new FVISNode('SONE_OBJECT_COMMENTS', 0, $env->get('VIS'));
        $node->addDataArray(array(
            'actionState' => $this->actionState,
            'path'        => $this->path,
            'allowAdd'    => $allowAddComment && $this->commentsAllowed ? 1 : null,
        ));
        if ($this->comments) {
            $comments = $this->comments;

            if ($commentsPerPage) {
                $totalPages = ceil(count($comments)/$commentsPerPage);
                if ($totalPages > 1) {
                    $curPage = (int) $env->request->getNumber('commentsPage');
                    $curPage = max(1, min($curPage, $totalPages));
                    $comments = array_slice($comments, $commentsPerPage*($curPage - 1), $commentsPerPage);
                    $node->appendChild('pages', $pagesNode = new FVISNode('SONE_OBJECT_COMMENTS_PAGE', FVISNode::VISNODE_ARRAY, $env->get('VIS')));
                    $pages = array();
                    for ($i = 1; $i <= $totalPages; $i++) {
                        $pages[] = array(
                            'path' => $this->path,
                            'actionState' => $this->actionState,
                            'page' => $i,
                            'current' => ($i == $curPage) ? 1 : null,
                        );
                    }
                    $pagesNode->addDataArray($pages);
                }
            }

            $uIds = array_unique(F2DArray::cols($comments, 'author_id'));
            /* @var SOne_Repository_User $users */
            $users = SOne_Repository_User::getInstance($env->get('db'));
            $userNames = $users->loadNames(array('id' => $uIds));
            foreach ($comments as &$comment) {
                $comment['author_name'] = isset($userNames[$comment['author_id']]) ? $userNames[$comment['author_id']] : null;
            }

            $node->appendChild('comments', $commentsNode = new FVISNode('SONE_OBJECT_COMMENTS_ITEM', FVISNode::VISNODE_ARRAY, $env->get('VIS')));

            $commentsNode->addDataArray($comments);
        }

        return $node;
    }

    public function loadExtraData(FDataBase $db)
    {
        if (!$this->pool['id']) {
            return;
        }

        $this->setComments(SOne_Repository_Comment::getInstance($db)->loadAll(array('object_id' => $this->pool['id'])));
    }

    public function saveExtraData(FDataBase $db)
    {
        if (!$this->pool['id']) {
            return;
        }

        /* @var SOne_Repository_Comment $repo */
        $repo = SOne_Repository_Comment::getInstance($db);
        foreach ($this->pool['comments'] as &$comment) {
            $comment['object_id'] = $this->pool['id'];
            if ($comment['answer_to']) {
                $comment['answer_to'] = $this->pool['comments'][$comment['answer_to']]['id'];
            }
            $repo->save($comment);
        }

        $this->setComments($this->pool['comments']);
    }
}
