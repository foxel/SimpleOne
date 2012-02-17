<?php

class SOne_Model_Object_Poll extends SOne_Model_Object
{
    const QUESTION_TYPE_TEXT = 1;
    const QUESTION_TYPE_SELECT = 2;
    const QUESTION_TYPE_BOOLEAN = 3;

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

        $curAnswers = (isset($data['answers'][$env->get('user')->id]))
            ? $data['answers'][$env->get('user')->id]
            : array();

        switch ($this->actionState) {
            case 'stat':
                $pollItemVisClass = 'SONE_OBJECT_POLL_ITEM_STAT';
                break;
            case 'edit':
                $pollItemVisClass = 'SONE_OBJECT_POLL_ITEM_EDIT';
                break;
            default:
                $pollItemVisClass = 'SONE_OBJECT_POLL_ITEM';
        }

        $pollLocked = true;

        foreach($data['questions'] as $qId => &$question) {
            $answerValue = isset($curAnswers[$qId])
                ? $curAnswers[$qId]
                : null;

            $node->appendChild('question_items', $item = new FVISNode($pollItemVisClass, 0, $env->get('VIS')));
            $item->addDataArray(array(
                'id'     => $qId,
                'locked' => ($questionLocked = $data['lockAnswers'] && isset($question['valueVariants'][$answerValue])) ? 1 : $pollLocked = null,
            ) + (array) $question);
            // TODO: other types of answers
            foreach ($question['valueVariants'] as $valueVariant => $valueTitle) {
                $item->appendChild('variants', $variantItem = new FVISNode($pollItemVisClass.'_VALUEVARIANT', 0, $env->get('VIS')));
                $variantItem->addDataArray(array(
                    'qId'      => $qId,
                    'value'    => $valueVariant,
                    'title'    => $valueTitle,
                    'selected' => ($valueVariant == $answerValue) ? 1 : null,
                    'locked'   => ($questionLocked) ? 1 : null,
                ));
            }
        }

        $node->addDataArray($this->pool + array(
            'locked'        => $pollLocked,
            'ask_for_login' => $env->get('user')->id ? null : 1,
            'canEdit'       => $this->isActionAllowed('edit', $env->get('user')) ? 1 : null,
        ));
        return $node;
    }

    public function setData(array $data)
    {
        $this->pool['data'] = $data + array(
            'questions'   => array(),
            'answers'     => array(),
            'lockAnswers' => false,
        );
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
            $curAnswers = isset($data['answers'][$env->get('user')->id])
                ? $data['answers'][$env->get('user')->id]
                : array();

            foreach($data['questions'] as $qId => &$question) {
                if ($data['lockAnswers'] && isset($curAnswers[$qId]) && isset($question['valueVariants'][$curAnswers[$qId]])) {
                    continue;
                }

                $answerValue = $env->request->getString('question_'.$qId.'_answer', K3_Request::POST);
                if (isset($question['valueVariants'][$answerValue])) {
                    $curAnswers[$qId] = $answerValue;
                }
                $data['answers'][$env->get('user')->id] = $curAnswers;
            }

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
                $newVariants = array();

                if (empty($newVariantsRaw)
                    || !isset($newQuestionRaw['caption'])  || empty($newQuestionRaw['caption'])
                    || !isset($newQuestionRaw['variants']) || empty($newQuestionRaw['variants'])) {
                    continue;
                }

                if (isset($oldQuestions[$qId])) {
                    $oldVariants = $oldQuestions[$qId]['valueVariants'];
                } else {
                    $oldVariants = array();
                    $qId = FStr::shortUID();
                }

                foreach ($newVariantsRaw as $aId => $aTitle) {
                    if (!strlen($aTitle)) {
                        continue;
                    }
                    if (!isset($oldVariants[$aId])) {
                        $aId = FStr::shortUID();
                    }
                    $newVariants[$aId] = $aTitle;
                }

                $newQuestions[$qId] = array(
                    'caption' => $newQuestionRaw['caption'],
                    'valueVariants' => $newVariants,
                );
            }

            $this->pool['questions'] = $newQuestions;
            $this->pool['updateTime'] = time();

            $updated = true;
        }
    }
}
