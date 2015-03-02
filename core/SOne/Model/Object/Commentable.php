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
 * @property boolean $commentsAllowed
 * @property array   $comments
 */
abstract class SOne_Model_Object_Commentable extends SOne_Model_Object implements SOne_Interface_Object_WithExtraData
{
    /**
     * @param array $init
     */
    public function __construct(array $init = array())
    {
        parent::__construct($init);
        $this->pool += array(
            'commentsAllowed' => true
        );

        $this->setComments(isset($init['comments']) ? $init['comments'] : array());
    }

    /**
     * This should be introduced in clild classes
     * @param  string $action
     * @param  SOne_Environment $env
     * @param  boolean &$objectUpdated
     */
    public function doAction($action, SOne_Environment $env, &$objectUpdated = false)
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
                        'client_ip' => $env->client->IPInteger,
                        'author_id' => $env->getUser()->id,
                    )
                );
                $objectUpdated = true;
                break;
        }
    }

    /**
     * @param string $text
     * @param int|null $answerTo
     * @param array $params
     * @return $this
     */
    public function addComment($text, $answerTo = null, array $params = array())
    {
        $tmpId = '_'.count($this->pool['comments']);
        if ($text) {
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
        }

        return $this;
    }

    /**
     * @param array $comments
     */
    public function setComments(array $comments)
    {
        $this->pool['comments'] = F2DArray::tree($comments, 'id', 'answer_to');
    }

    /**
     * @param SOne_Environment $env
     * @param bool $allowAddComment
     * @param bool $commentsPerPage
     * @return FVISNode
     */
    public function visualizeComments(SOne_Environment $env, $allowAddComment = true, $commentsPerPage = false)
    {
        $node = new FVISNode('SONE_OBJECT_COMMENTS', 0, $env->getVIS());
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

                    $paginator = new SOne_VIS_Paginator(array(
                        'objectPath'  => $this->path,
                        'pageVarName' => 'commentsPage',
                        'totalPages'  => $totalPages,
                        'currentPage' => $curPage,
                        'actionState' => $this->actionState,
                        'fragment'    => 'pageComments',
                    ));
                    $node->appendChild('paginator', $paginator->visualize($env));
                }
            }

            $uIds = array_unique(F2DArray::cols($comments, 'author_id'));
            $users = SOne_Repository_User::getInstance($env->getDb())->prepareFetch(array('id=' => $uIds));

            F2DArray::keycol($users, 'id');
            foreach ($comments as &$comment) {
                if ($user = $users->get($comment['author_id'])) {
                    $comment['author_name']   = $user->name;
                    $comment['author_email']  = $user->email;
                    $comment['author_avatar'] = $user->avatar;
                }
            }

            $node->appendChild('comments', $commentsNode = new FVISNode('SONE_OBJECT_COMMENTS_ITEM', FVISNode::VISNODE_ARRAY, $env->getVIS()));

            $commentsNode->addDataArray($comments);
        }

        return $node;
    }

    /**
     * @param K3_Db_Abstract $db
     */
    public function loadExtraData(K3_Db_Abstract $db)
    {
        if (!$this->pool['id']) {
            return;
        }

        $this->setComments(SOne_Repository_Comment::getInstance($db)->loadAll(array('object_id' => $this->pool['id'])));
    }

    /**
     * @param K3_Db_Abstract $db
     */
    public function saveExtraData(K3_Db_Abstract $db)
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
