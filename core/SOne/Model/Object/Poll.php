<?php

class SOne_Model_Object_Poll extends SOne_Model_Object
{
    const QUESTION_TYPE_TEXT = 1;
    const QUESTION_TYPE_SELECT = 2;
    const QUESTION_TYPE_BOOLEAN = 3;

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
            $answerValue = isset($curAnswers[$qId])
                ? $curAnswers[$qId]
                : null;

            $node->appendChild('question_items', $item = new FVISNode($pollItemVisClass, 0, $env->get('VIS')));
            $item->addDataArray(array(
                'id'       => $qId,
                'answered' => ($questionAnswered = isset($question['valueVariants'][$answerValue])) ? 1 : $pollAnswered = null,
                'locked'   => ($questionLocked = $question['lockAnswers'] && $questionAnswered) ? 1 : $pollLocked = null,
            ) + (array) $question);
            // TODO: other types of answers
            foreach ($question['valueVariants'] as $valueVariant => $valueTitle) {
                $valueLimit  = isset($question['valueLimits'][$valueVariant])
                    ? (int) $question['valueLimits'][$valueVariant]
                    : null;
                $valueCount  = isset($statAnswers[$qId][$valueVariant])
                    ? count($statAnswers[$qId][$valueVariant])
                    : 0;
                $valueLocked = ($questionLocked) || ($valueLimit && ($valueVariant != $answerValue) && $valueCount >= $valueLimit);

                $item->appendChild('variants', $variantItem = new FVISNode($pollItemVisClass.'_VALUEVARIANT', 0, $env->get('VIS')));
                $variantItem->addDataArray(array(
                    'qId'       => $qId,
                    'value'     => $valueVariant,
                    'title'     => $valueTitle,
                    'limit'     => $valueLimit,
                    'available' => $valueLimit ? ($valueLimit - $valueCount) : null,
                    'selected'  => ($valueVariant == $answerValue) ? 1 : null,
                    'answered'  => $questionAnswered ? 1 : null,
                    'locked'    => $valueLocked ? 1 : null,
                    'statVal'   => $valueCount,
                    'statUsers' => isset($statAnswers[$qId][$valueVariant]) ? implode(', ', $statAnswers[$qId][$valueVariant]) : null,
                ));
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
                if ($question['lockAnswers'] && isset($curAnswers[$qId]) && isset($question['valueVariants'][$curAnswers[$qId]])) {
                    continue;
                }

                $answerValue = $env->request->getString('question_'.$qId.'_answer', K3_Request::POST);

                // available count
                if (isset($question['valueLimits'][$answerValue])) {
                    $valueLimit = $question['valueLimits'][$answerValue];
                    $available  = isset($statAnswers[$qId][$answerValue])
                        ? $valueLimit - count($statAnswers[$qId][$answerValue])
                        : $valueLimit;

                    if (isset($curAnswers[$qId]) && $curAnswers[$qId] == $answerValue) {
                        $available++;
                    }
                    // no available
                    if ($available <= 0) {
                        continue;
                    }
                }
                // storing answer
                if (isset($question['valueVariants'][$answerValue])) {
                    $curAnswers[$qId] = $answerValue;
                }
            }

            $data['answers'][$env->get('user')->id] = $curAnswers;

            $updated = true;
        }

        if ($action == 'save') {
            $newQuestionsRaw = (array) $env->request->get('questions');
            $newQuestions = array();
            $oldQuestions = $this->pool['questions'];
            foreach ($newQuestionsRaw as $qId => $newQuestionRaw) {
                $newVariantsRaw = isset($newQuestionRaw['variants'])
                    ? (array) $newQuestionRaw['variants']
                    : array();
                $newLimitsRaw = isset($newQuestionRaw['limits'])
                    ? (array) $newQuestionRaw['limits']
                    : array();
                $newVariants = array();
                $newLimits   = array();

                if (empty($newVariantsRaw)
                    || !isset($newQuestionRaw['caption'])  || empty($newQuestionRaw['caption'])
                    || !isset($newQuestionRaw['variants']) || empty($newQuestionRaw['variants'])) {
                    continue;
                }

                if (isset($oldQuestions[$qId])) {
                    $oldVariants = (array) $oldQuestions[$qId]['valueVariants'];
                    $oldLimits   = (array) $oldQuestions[$qId]['valueLimits'];
                } else {
                    $oldVariants = array();
                    $oldLimits   = array();
                    $qId = FStr::shortUID();
                }

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

                $newQuestions[$qId] = array(
                    'caption'       => $newQuestionRaw['caption'],
                    'lockAnswers'   => isset($newQuestionRaw['lockAnswers']) && $newQuestionRaw['lockAnswers'] ? true : false,
                    'valueVariants' => $newVariants,
                    'valueLimits'   => $newLimits,
                );
            }

            $this->pool['questions']   = $newQuestions;
            $this->pool['description'] = $env->request->getString('description', K3_Request::POST);
            $this->pool['updateTime']  = time();

            $updated = true;
        }
    }

    protected function genAnswersStats(array $statUsers = array())
    {
        $data =& $this->pool['data'];

        $statAnswers = array();
        foreach ($data['answers'] as $uId => &$uAnswers) {
            foreach ($uAnswers as $qId => $aId) {
                $uName = isset($statUsers[$uId]) ? $statUsers[$uId]->name : $uId;
                if (!isset($statAnswers[$qId])) {
                    $statAnswers[$qId] = array(
                        $aId  => array($uName),
                    );
                } elseif (!isset($statAnswers[$qId][$aId])) {
                    $statAnswers[$qId][$aId] = array($uName);
                } else {
                    $statAnswers[$qId][$aId][] = $uName;
                }
            }
        }

        return $statAnswers;
    }
}
