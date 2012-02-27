<?php

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

    public function visualizeComments(K3_Environment $env, $allowAddComment = true)
    {
        $node = new FVISNode('SONE_OBJECT_COMMENTS', 0, $env->get('VIS'));
        $node->addDataArray(array(
            'actionState' => $this->actionState,
            'path'        => $this->path,
            'allowAdd'    => $allowAddComment && $this->commentsAllowed ? 1 : null,
        ));
        if ($this->comments) {
            $comments = $this->comments;

            $uIds = array_unique(F2DArray::cols($comments, 'author_id'));
            $userNames = SOne_Repository_User::getInstance($env->get('db'))->loadNames(array('id' => $uIds));
            foreach ($comments as &$comment) {
                $comment['author_name'] = isset($userNames[$comment['author_id']]) ? $userNames[$comment['author_id']] : null;
            }

            $node->appendChild('comments', $commentsNode = new FVISNode('SONE_OBJECT_COMMENTS_ITEM', FVISNode::VISNODE_ARRAY, $env->get('VIS')));
            $commentsNode->addDataArray($comments);
            unset($data['comments']);
        }

        return $node;
    }

    public function loadExtraData(FDataBase $db)
    {
        if (!$this->pool['id']) {
            return false;
        }

        $this->setComments(SOne_Repository_Comment::getInstance($db)->loadAll(array('object_id' => $this->pool['id'])));
    }

    public function saveExtraData(FDataBase $db)
    {
        if (!$this->pool['id']) {
            return false;
        }

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
