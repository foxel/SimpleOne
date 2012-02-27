<?php

class SOne_Model_Comment extends SOne_Model
{
    public $id        = null;
    public $answerTo  = null;
    public $time      = null;
    public $clientIp  = null;
    public $text      = '';
    public $treeLevel = null;

    public function __construct(array $init = array())
    {
        $this->id        = isset($init['id'])        ? $init['id']              : null;
        $this->answerTo  = isset($init['answer_to']) ? $init['answer_to']       : null;
        $this->time      = isset($init['time'])      ? (int) $init['time']      : time();
        $this->clientIp  = isset($init['client_ip']) ? (int) $init['client_ip'] : null;
        $this->text      = isset($init['text'])      ? (string) $init['text']   : '';
        $this->treeLevel = isset($init['t_level'])   ? (int) $init['t_level']   : null;
    }
}
