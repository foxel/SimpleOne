<?php

class SOne_Model_Object_Poll extends SOne_Model_Object
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
    protected $aclEditActionsList = array('edit', 'save', 'stat');

    /**
     * @param  array $init
     */
    public function __construct(array $init = array())
    {
        parent::__construct($init);

        $this->setData((array) $this->pool['data']);
    }

    public function visualize(K3_Environment $env)
    {
        if (in_array($this->actionState, array('save', 'fill'))) {
            $env->response->sendRedirect($this->path);
        }

        $node = new FVISNode('SONE_OBJECT_POLL', 0, $env->get('VIS'));
        $data =& $this->pool['data'];

        $statUsers = array();
        switch ($this->actionState) {
            case 'stat':
                $pollItemVisClass = 'SONE_OBJECT_POLL_ITEM_STAT';
                $statUsers = !empty($data['answers']) ? SOne_Repository_User::getInstance($env->get('db'))->loadAll(array('id' => array_keys($data['answers']))) : array();
                break;
            case 'edit':
                $pollItemVisClass = 'SONE_OBJECT_POLL_ITEM_EDIT';
                break;
            default:
                $pollItemVisClass = 'SONE_OBJECT_POLL_ITEM';
        }

        $statAnswers = $this->genAnswersStats($statUsers);
        $curAnswers = (isset($data['answers'][$env->get('user')->id]))
            ? $data['answers'][$env->get('user')->id]
            : array();

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

            $node->appendChild('question_items', $item = new FVISNode($pollItemVisClass, 0, $env->get('VIS')));
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
                    $item->addDataArray(array(
                        'statUsers' => isset($statAnswers[$qId]) ? implode(', ', $statAnswers[$qId]) : null,
                        'statVal'   => isset($statAnswers[$qId]) ? count($statAnswers[$qId]) : 0,
                    ));
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

                        $item->appendChild('variants', $variantItem = new FVISNode($pollItemVisClass.'_VALUEVARIANT', 0, $env->get('VIS')));
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
            'ask_for_login' => $env->get('user')->id ? null : 1,
            'canEdit'       => $this->isActionAllowed('edit', $env->get('user')) ? 1 : null,
        ));
        return $node;
    }

    public function setData(array $data)
    {
        $this->pool['data'] = $data + array(
            'description' => '',
            'questions'   => array(),
            'answers'     => array(),
            'lockAnswers' => false,
        );
        $this->pool['description'] =& $this->pool['data']['description'];
        $this->pool['questions']   =& $this->pool['data']['questions'];
        $this->pool['answers']     =& $this->pool['data']['answers'];
        $this->pool['lockAnswers'] =& $this->pool['data']['lockAnswers'];
        return $this;
    }

    public function doAction($action, K3_Environment $env, &$updated = false)
    {
        parent::doAction($action, $env, $updated);
        $data =& $this->pool['data'];

        if ($action == 'fill' && !empty($data['questions']) && $env->get('user')->id) {
            $statAnswers = $this->genAnswersStats();
            $curAnswers = isset($data['answers'][$env->get('user')->id])
                ? $data['answers'][$env->get('user')->id]
                : array();

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
                        $rawAnswerValues = (array) $env->request->get('question_'.$qId.'_answer', K3_Request::POST);
                        foreach ($rawAnswerValues as $rawAnswerValue) {
                            // available count
                            if (isset($question['valueLimits'][$rawAnswerValue])) {
                                $valueLimit = $question['valueLimits'][$rawAnswerValue];
                                $available  = isset($statAnswers[$qId][$rawAnswerValue])
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

            $data['answers'][$env->get('user')->id] = $curAnswers;

            $updated = true;
        }
    }

    protected function saveAction(K3_Environment $env, &$updated = false)
    {
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
                $qId         = FStr::shortUID();
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
                            : FStr::shortUID();

                        $newVariants[$aId] = $aTitle;
                        if (isset($newLimitsRaw[$aIdRaw]) && $newLimitsRaw[$aIdRaw]) {
                            $newLimits[$aId] = (int) $newLimitsRaw[$aIdRaw];
                        }
                    }
            }

            $newQuestions[$qId] = array(
                'caption'       => $newQuestionRaw['caption'],
                'lockAnswers'   => isset($newQuestionRaw['lockAnswers']) && $newQuestionRaw['lockAnswers'] ? true : false,
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

    protected function genAnswersStats(array $statUsers = array())
    {
        $answers   =& $this->pool['answers'];
        $questions =& $this->pool['questions'];

        $statAnswers = array();
        foreach ($answers as $uId => &$uAnswers) {
            $uName = isset($statUsers[$uId]) ? $statUsers[$uId]->name : $uId;

            foreach ($uAnswers as $qId => $ansverValue) {
                if (!isset($questions[$qId]) || is_null($ansverValue)) {
                    continue;
                }

                if (!isset($statAnswers[$qId])) {
                    $statAnswers[$qId] = array();
                }

                switch ($questions[$qId]['type']) {
                    case self::QUESTION_TYPE_TEXT:
                    case self::QUESTION_TYPE_STRING:
                        $statAnswers[$qId][] = $uName;
                        break;
                    case self::QUESTION_TYPE_MULTI:
                    case self::QUESTION_TYPE_SELECT:
                    default:
                        foreach ((array) $ansverValue as $aId) {
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
