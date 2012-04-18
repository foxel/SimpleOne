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
