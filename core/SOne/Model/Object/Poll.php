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
 * @property-read string $description
 * @property-read array[] $questions
 * @property-read array[] $answers
 */
class SOne_Model_Object_Poll extends SOne_Model_Object
    implements SOne_Interface_Object_Structured
{
    const QUESTION_TYPE_SELECT = 1;
    const QUESTION_TYPE_MULTI = 2;
    const QUESTION_TYPE_TEXT = 3;
    const QUESTION_TYPE_STRING = 4;

    protected static $_questionTypes = array(
        self::QUESTION_TYPE_SELECT,
        self::QUESTION_TYPE_MULTI,
        self::QUESTION_TYPE_TEXT,
        self::QUESTION_TYPE_STRING,
    );

    /**
     * @var array
     */
    protected $aclEditActionsList = array('edit', 'save', 'stat', 'drop', 'grid', 'clean');

    /**
     * @param SOne_Environment $env
     * @return FVISNode
     */
    public function visualize(SOne_Environment $env)
    {
        if (in_array($this->actionState, array('save', 'fill', 'drop', 'clean'))) {
            $env->response->sendRedirect($this->path);
        }

        if ($this->actionState == 'grid') {
            return $this->visualizeGrid($env);
        }

        $node = new FVISNode('SONE_OBJECT_POLL', 0, $env->getVIS());
        $data =& $this->pool['data'];

        $statUsers = array();
        switch ($this->actionState) {
            case 'stat':
                $pollItemVisClass = 'SONE_OBJECT_POLL_ITEM_STAT';
                $statUsers = !empty($data['answers']) ? SOne_Repository_User::getInstance($env->getDb())->loadAll(array('id' => array_keys($data['answers']))) : array();
                break;
            case 'edit':
                $pollItemVisClass = 'SONE_OBJECT_POLL_ITEM_EDIT';
                break;
            default:
                $pollItemVisClass = 'SONE_OBJECT_POLL_ITEM';
        }

        $statAnswers = $this->_genAnswersStats($statUsers);
        $curAnswers  = $this->_getCurrentAnswers($env);

        $pollLocked   = true;
        $pollAnswered = true;

        foreach($data['questions'] as $qId => &$question) {
            $questionType = isset($question['type'])
                ? (int)$question['type']
                : self::QUESTION_TYPE_SELECT;
            $answerValue = isset($curAnswers[$qId])
                ? $curAnswers[$qId]
                : null;

            switch ($questionType) {
                case self::QUESTION_TYPE_TEXT:
                case self::QUESTION_TYPE_STRING:
                    $questionAnswered = !is_null($answerValue);
                    break;
                case self::QUESTION_TYPE_MULTI:
                    $questionAnswered = (bool) count(array_intersect((array)$answerValue, array_keys($question['valueVariants'])));
                    break;
                case self::QUESTION_TYPE_SELECT:
                default:
                    $questionAnswered = isset($question['valueVariants'][$answerValue]);
            }

            $questionLocked   = $question['lockAnswers'] && $questionAnswered;

            $node->appendChild('question_items', $item = new FVISNode($pollItemVisClass, 0, $env->getVIS()));
            $item->addDataArray(array(
                'id'          => $qId,
                'answered'    => $questionAnswered ? 1 : $pollAnswered = null,
                'locked'      => $questionLocked   ? 1 : $pollLocked = null,
                'answerValue' => $answerValue,
            ) + (array) $question);

            // TODO: other types of answers
            switch ($questionType) {
                case self::QUESTION_TYPE_TEXT:
                case self::QUESTION_TYPE_STRING:
                    if (isset($statAnswers[$qId]) && !empty($statAnswers[$qId])) {
                        $item->addDataArray(array(
                            'statUsers' => implode(', ', array_keys($statAnswers[$qId])),
                            'statVal'   => count($statAnswers[$qId]),
                        ));
                        $answers = array();
                        foreach ($statAnswers[$qId] as $username => $answerValue) {
                            $answers[] = array('userName' => $username, 'answerValue' => $answerValue);
                        }
                        $item->appendChild('variants', $answersItem = new FVISNode($pollItemVisClass.'_TEXTANSWER', FVISNode::VISNODE_ARRAY, $env->getVIS()));
                        $answersItem->addDataArray($answers);
                    } else {
                        $item->addData('statVal', 0);
                    }
                    break;
                case self::QUESTION_TYPE_MULTI:
                case self::QUESTION_TYPE_SELECT:
                default:
                    foreach ($question['valueVariants'] as $valueVariant => $valueTitle) {
                        $valueLimit  = isset($question['valueLimits'][$valueVariant])
                            ? (int) $question['valueLimits'][$valueVariant]
                            : null;
                        $valueCount  = isset($statAnswers[$qId][$valueVariant])
                            ? count($statAnswers[$qId][$valueVariant])
                            : 0;

                        $valueSelected = $questionAnswered && in_array($valueVariant, (array)$answerValue);
                        $valueLocked   = ($questionLocked) || ($valueLimit && !$valueSelected && $valueCount >= $valueLimit);

                        $item->appendChild('variants', $variantItem = new FVISNode($pollItemVisClass.'_VALUEVARIANT', 0, $env->getVIS()));
                        $variantItem->addDataArray(array(
                            'qId'       => $qId,
                            'value'     => $valueVariant,
                            'title'     => $valueTitle,
                            'limit'     => $valueLimit,
                            'available' => $valueLimit ? ($valueLimit - $valueCount) : null,
                            'selected'  => $valueSelected ? 1 : null,
                            'answered'  => $questionAnswered ? 1 : null,
                            'locked'    => $valueLocked ? 1 : null,
                            'statVal'   => $valueCount,
                            'statUsers' => isset($statAnswers[$qId][$valueVariant]) ? implode(', ', $statAnswers[$qId][$valueVariant]) : null,
                            'isCheckbox' => $question['type'] == self::QUESTION_TYPE_MULTI,
                        ));
                    }
            }
        }

        $node->addDataArray($this->pool + array(
            'locked'        => $pollLocked,
            'answered'      => $pollAnswered,
            'ask_for_login' => $this->isActionAllowed('fill', $env->getUser()) ? null : 1,
            'canEdit'       => $this->isActionAllowed('edit', $env->getUser()) ? 1 : null,
        ));
        return $node;
    }

    /**
     * @param string $action
     * @param SOne_Model_User $user
     * @return bool
     */
    public function isActionAllowed($action, SOne_Model_User $user)
    {
        return ($action == 'fill')
            ? $user->id > 0
            : parent::isActionAllowed($action, $user);
    }

    /**
     * @param SOne_Environment $env
     * @return FVISNode
     */
    public function visualizeGrid(SOne_Environment $env)
    {
        $node = new FVISNode('SONE_OBJECT_POLL_GRID', 0, $env->getVIS());

        $answers   =& $this->pool['answers'];
        $questions =& $this->pool['questions'];
        $statUsers = !empty($answers) ? SOne_Repository_User::getInstance($env->getDb())->loadAll(array('id' => array_keys($answers))) : array();

        $node->appendChild('gridHeaders', $gridHeaders = new FVISNode('SONE_OBJECT_POLL_GRID_HEADER', FVISNode::VISNODE_ARRAY, $env->getVIS()));
        $gridHeaders->addDataArray($questions);

        foreach ($answers as $uId => &$uAnswers) {
            $uName = isset($statUsers[$uId]) ? $statUsers[$uId]->name : $uId;

            $node->appendChild('gridRows', $gridRow = new FVISNode('SONE_OBJECT_POLL_GRID_ROW', 0, $env->getVIS()));
            $gridRow->addData('userName', $uName);

            $values = array();
            foreach ($questions as $qId => $question) {
                if (!isset($uAnswers[$qId])) {
                    $values[] = array();
                    continue;
                }

                $answerValue = $uAnswers[$qId];
                switch ($question['type']) {
                    case self::QUESTION_TYPE_TEXT:
                    case self::QUESTION_TYPE_STRING:
                        $values[] = array(
                            'value' => $answerValue,
                        );
                        break;
                    case self::QUESTION_TYPE_MULTI:
                    case self::QUESTION_TYPE_SELECT:
                    default:
                        $value = array();
                        foreach ((array) $answerValue as $aId) {
                            if (isset($question['valueVariants'][$aId])) {
                                $value[] = $question['valueVariants'][$aId];
                            }
                        }
                        $values[] = array(
                            'value' => implode(', ', $value),
                        );
                }
            }
            $gridRow->appendChild('answers', $gridAnswers = new FVISNode('SONE_OBJECT_POLL_GRID_COL', FVISNode::VISNODE_ARRAY, $env->getVIS()));
            $gridAnswers->addDataArray($values);
        }

        $node->addDataArray($this->pool + array(
            'canEdit'       => $this->isActionAllowed('edit', $env->getUser()) ? 1 : null,
        ));

        return $node;
    }

    /**
     * @param array $data
     * @return SOne_Model_Object_Poll
     */
    public function setData(array $data)
    {
        $this->pool['data'] = $data + array(
            'description' => '',
            'questions'   => array(),
            'answers'     => array(),
        );
        $this->pool['description'] =& $this->pool['data']['description'];
        $this->pool['questions']   =& $this->pool['data']['questions'];
        $this->pool['answers']     =& $this->pool['data']['answers'];
        return $this;
    }

    /**
     * @param SOne_Environment $env
     * @param bool $updated
     */
    protected function fillAction(SOne_Environment $env, &$updated = false)
    {
        if (!empty($this->questions) && $env->getUser()->id) {
            $curAnswers = $this->_fillAnswers($env);

            $this->pool['data']['answers'][$env->getUser()->id] = $curAnswers;

            $updated = true;
        }
    }

    /**
     * @param SOne_Environment $env
     * @return array
     */
    protected function _fillAnswers(SOne_Environment $env)
    {
        $data =& $this->data;
        $statAnswers = $this->_genAnswersStats();
        $curAnswers = $this->_getCurrentAnswers($env, $data);

        foreach ($data['questions'] as $qId => $question) {
            $questionType = isset($question['type'])
                ? (int)$question['type']
                : self::QUESTION_TYPE_SELECT;

            $answerValue = isset($curAnswers[$qId])
                ? $curAnswers[$qId]
                : null;

            switch ($questionType) {
                case self::QUESTION_TYPE_TEXT:
                case self::QUESTION_TYPE_STRING:
                    $questionAnswered = !is_null($answerValue);
                    break;
                case self::QUESTION_TYPE_MULTI:
                    $questionAnswered = (bool)count(array_intersect((array)$answerValue, array_keys($question['valueVariants'])));
                    break;
                case self::QUESTION_TYPE_SELECT:
                default:
                    $questionAnswered = isset($question['valueVariants'][$answerValue]);

            }
            $questionLocked = $question['lockAnswers'] && $questionAnswered;

            if ($questionLocked) {
                continue;
            }

            $answerValue = null;

            switch ($questionType) {
                case self::QUESTION_TYPE_TEXT:
                case self::QUESTION_TYPE_STRING:
                    $answerValue = $env->request->getString('question_'.$qId.'_answer', K3_Request::POST);
                    break;
                case self::QUESTION_TYPE_MULTI:
                case self::QUESTION_TYPE_SELECT:
                default:
                    $answerValues = array();
                    $rawAnswerValues = (array)$env->request->get('question_'.$qId.'_answer', K3_Request::POST);
                    foreach ($rawAnswerValues as $rawAnswerValue) {
                        // available count
                        if (isset($question['valueLimits'][$rawAnswerValue])) {
                            $valueLimit = $question['valueLimits'][$rawAnswerValue];
                            $available = isset($statAnswers[$qId][$rawAnswerValue])
                                ? $valueLimit - count($statAnswers[$qId][$rawAnswerValue])
                                : $valueLimit;

                            if (isset($curAnswers[$qId]) && $curAnswers[$qId] == $rawAnswerValue) {
                                $available++;
                            }
                            // no available
                            if ($available <= 0) {
                                continue;
                            }
                        }

                        if (isset($question['valueVariants'][$rawAnswerValue])) {
                            $answerValues[] = $rawAnswerValue;
                        }
                    }

                    $answerValue = ($questionType == self::QUESTION_TYPE_SELECT)
                        ? array_shift($answerValues)
                        : $answerValues;
            }

            // storing answer
            if (!empty($answerValue)) {
                $curAnswers[$qId] = $answerValue;
            } elseif ($env->request->getBinary('question_'.$qId.'_active', K3_Request::POST)) {
                unset($curAnswers[$qId]);
            }
        }

        return $curAnswers;
    }

    /**
     * @param SOne_Environment $env
     * @return array
     */
    protected function _getCurrentAnswers(SOne_Environment $env)
    {
        $curAnswers = isset($this->answers[$env->getUser()->id])
            ? $this->answers[$env->getUser()->id]
            : array();
        return $curAnswers;
    }

    /**
     * @param SOne_Environment $env
     * @param bool $updated
     */
    protected function saveAction(SOne_Environment $env, &$updated = false)
    {
        parent::saveAction($env, $updated);

        $newQuestionsRaw = (array) $env->request->get('questions');
        $newQuestions = array();
        $oldQuestions = (array) $this->pool['questions'];

        foreach ($newQuestionsRaw as $qId => $newQuestionRaw) {
            if (!isset($newQuestionRaw['caption'])  || empty($newQuestionRaw['caption'])) {
                continue;
            }

            $newQuestionType = isset($newQuestionRaw['type']) && in_array((int) $newQuestionRaw['type'], self::$_questionTypes)
                ? (int) $newQuestionRaw['type']
                : self::QUESTION_TYPE_SELECT;
            $newVariantsRaw = isset($newQuestionRaw['variants'])
                ? (array) $newQuestionRaw['variants']
                : array();
            $newLimitsRaw = isset($newQuestionRaw['limits'])
                ? (array) $newQuestionRaw['limits']
                : array();
            $newVariants = array();
            $newLimits   = array();

            // no variants for select answer questions
            if (in_array($newQuestionType, array(self::QUESTION_TYPE_SELECT, self::QUESTION_TYPE_MULTI)) && empty($newVariantsRaw)) {
                continue;
            }

            if (isset($oldQuestions[$qId])) {
                $oldVariants = (array) $oldQuestions[$qId]['valueVariants'];
            } else {
                $oldVariants = array();
                $qId         = K3_Util_String::shortUID();
            }

            // collecting value variants
            switch ($newQuestionType) {
                case self::QUESTION_TYPE_TEXT:
                case self::QUESTION_TYPE_STRING:
                    // no variants for this types
                    break;
                case self::QUESTION_TYPE_MULTI:
                case self::QUESTION_TYPE_SELECT:
                default:
                    foreach ($newVariantsRaw as $aIdRaw => $aTitle) {
                        if (!strlen($aTitle)) {
                            continue;
                        }
                        $aId = isset($oldVariants[$aIdRaw])
                            ? $aIdRaw
                            : K3_Util_String::shortUID();

                        $newVariants[$aId] = $aTitle;
                        if (isset($newLimitsRaw[$aIdRaw]) && $newLimitsRaw[$aIdRaw]) {
                            $newLimits[$aId] = (int) $newLimitsRaw[$aIdRaw];
                        }
                    }
            }

            $newQuestions[$qId] = array(
                'caption'       => $newQuestionRaw['caption'],
                'lockAnswers'   => isset($newQuestionRaw['lockAnswers']) ? (bool)$newQuestionRaw['lockAnswers'] : false,
                'valueVariants' => $newVariants,
                'valueLimits'   => $newLimits,
                'type'          => $newQuestionType,
            );
        }

        $this->pool['questions']   = $newQuestions;
        $this->pool['description'] = $env->request->getString('description', K3_Request::POST);
        $this->pool['updateTime']  = time();

        $updated = true;
    }

    protected function dropAction(SOne_Environment $env, &$updated = false)
    {
        $data =& $this->pool['data'];

        $userId = $env->request->getNumber('userId');

        if (isset($data['answers'][$userId])) {
            unset($data['answers'][$userId]);
            $updated = true;
        }
    }

    /**
     * @param SOne_Environment $env
     * @param bool $updated
     */
    protected function cleanAction(SOne_Environment $env, &$updated = false)
    {
        if (!$env->request->isPost) {
            $env->response->sendRedirect($this->path);
        }

        $data =& $this->pool['data'];
        $data['answers'] = array();
        $updated = true;
    }

    /**
     * @param array $statUsers
     * @return array
     */
    protected function _genAnswersStats(array $statUsers = array())
    {
        $answers   =& $this->pool['answers'];
        $questions =& $this->pool['questions'];

        $statAnswers = array();
        foreach ($answers as $uId => &$uAnswers) {
            $uName = isset($statUsers[$uId]) ? $statUsers[$uId]->name : $uId;

            foreach ($uAnswers as $qId => $answerValue) {
                if (!isset($questions[$qId]) || is_null($answerValue)) {
                    continue;
                }

                if (!isset($statAnswers[$qId])) {
                    $statAnswers[$qId] = array();
                }

                switch ($questions[$qId]['type']) {
                    case self::QUESTION_TYPE_TEXT:
                    case self::QUESTION_TYPE_STRING:
                        $statAnswers[$qId][$uName] = $answerValue;
                        break;
                    case self::QUESTION_TYPE_MULTI:
                    case self::QUESTION_TYPE_SELECT:
                    default:
                        foreach ((array) $answerValue as $aId) {
                            if (!isset($statAnswers[$qId][$aId])) {
                                $statAnswers[$qId][$aId] = array($uName);
                            } else {
                                $statAnswers[$qId][$aId][] = $uName;
                            }
                        }
                }
            }
        }

        return $statAnswers;
    }
}
