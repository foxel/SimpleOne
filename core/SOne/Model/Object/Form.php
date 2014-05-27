<?php
/**
 * Copyright (C) 2013 Andrey F. Kupreychik (Foxel)
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
 * Class SOne_Model_Object_Form
 *
 * @property-read string $targetEmail
 * @author Foxel
 */
class SOne_Model_Object_Form extends SOne_Model_Object_Poll
{
    /**
     * @param SOne_Environment $env
     * @return FVISNode
     * @author Foxel
     */
    public function visualize(SOne_Environment $env)
    {
        if ($this->actionState == 'captcha') {
            $captcha = new K3_Captcha($env);
            $env->response
                ->write($captcha->generate())
                ->setDoHTMLParse(false)
                ->sendBuffer(F_INTERNAL_ENCODING, array('contentType' => image_type_to_mime_type(IMG_JPEG), 'contentCacheTime' => 1));
        }
        $node = parent::visualize($env);

        $node->addData('captchaNeeded', $env->user->id ? null : 1);
        if ($env->session->get('formFilled'.K3_Util_String::shortHash($this->path))) {
            $env->session->drop('formFilled'.K3_Util_String::shortHash($this->path));
            $node->addData('formSent', 1);
        }

        $node->setType('SONE_OBJECT_FORM');

        return $node;
    }

    /**
     * @param SOne_Environment $env
     * @param bool $updated
     * @author Foxel
     */
    protected function fillAction(SOne_Environment $env, &$updated = false)
    {
        if (!empty($this->questions)) {
            $captcha = new K3_Captcha($env);
            $curAnswers = $this->_fillAnswers($env);
            if ($env->user->id || $captcha->check($env->request->getString('captchaString', K3_Request::POST))) {
                // send email here
                $mail = new FMail('Заполнена форма "'.$this->caption.'"', 'SimpleOne', 'no-reply@'.$env->server->domain, $env);
                $mail->addTo($this->targetEmail);
                $mail->setBody($this->_formatMailBody($env, $curAnswers), true);
                if ($mail->send()) {
                    $env->session->drop('formAnswers'.K3_Util_String::shortHash($this->path));
                    $env->session->set('formFilled'.K3_Util_String::shortHash($this->path), true);
                } else {
                    $this->pool['errors'] = '<ul><li>Ошибка отправки</ul></li>';
                    $this->pool['actionState'] = '';
                }
            } else {
                $env->session->set('formAnswers'.K3_Util_String::shortHash($this->path), $curAnswers);
                $this->pool['errors'] = '<ul><li>Неверно введен код защиты</ul></li>';
                $this->pool['actionState'] = '';
            }
        }
    }

    /**
     * @param SOne_Environment $env
     * @param array $curAnswers
     * @return string
     */
    protected function _formatMailBody(SOne_Environment $env, array $curAnswers)
    {
        $node = new FVISNode('SONE_OBJECT_FORM_MAIL_BODY', 0, $env->getVIS());
        $node->addDataArray(array(
            'clientIP' => $env->client->IP,
        ) + $this->pool);
        $rows = array();
        foreach ($this->questions as $qId => $question) {
            $answerValue = isset($curAnswers[$qId]) ? $curAnswers[$qId] : null;
            $line = array(
                'question' => $question['caption'],
            );
            switch ($question['type']) {
                case self::QUESTION_TYPE_TEXT:
                case self::QUESTION_TYPE_STRING:
                    $line['answer'] = $answerValue;
                    break;
                case self::QUESTION_TYPE_MULTI:
                case self::QUESTION_TYPE_SELECT:
                default:
                    $line['answer'] = implode(', ', array_intersect_key($question['valueVariants'], array_flip((array) $answerValue)));
            }
            $rows[] = $line;
        }
        $rowsNode = new FVISNode('SONE_OBJECT_FORM_MAIL_ROW', FVISNode::VISNODE_ARRAY, $env->getVIS());
        $rowsNode->addDataArray($rows);
        $node->appendChild('answers', $rowsNode);

        return $node->parse();
    }

    /**
     * @param SOne_Environment $env
     * @param bool $updated
     */
    protected function saveAction(SOne_Environment $env, &$updated = false)
    {
        parent::saveAction($env, $updated);

        if ($updated) {
            $this->pool['targetEmail'] = $env->request->getString('target_email', K3_Request::POST, K3_Util_String::FILTER_LINE);
        }
    }


    /**
     * @param SOne_Environment $env
     * @return array
     */
    protected function _getCurrentAnswers(SOne_Environment $env)
    {
        return (array)$env->session->get('formAnswers'.K3_Util_String::shortHash($this->path));
    }

    /**
     * @param string $action
     * @param SOne_Model_User $user
     * @return bool
     */
    public function isActionAllowed($action, SOne_Model_User $user)
    {
        switch ($action) {
            case 'fill':
                return true;
            case 'stat':
                return false;
            default:
                return parent::isActionAllowed($action, $user);
        }
    }

    /**
     * @param array $data
     * @return $this|SOne_Model_Object_Poll
     */
    public function setData(array $data)
    {
        parent::setData($data);

        $this->pool['data'] += array(
            'targetEmail' => '',
        );
        $this->pool['targetEmail'] =& $this->pool['data']['targetEmail'];
        $this->pool['data']['answers'] = array();
        return $this;
    }


}
