<?php

namespace UJM\ExoBundle\Library\Item\Definition;

use UJM\ExoBundle\Entity\Attempt\Answer;
use UJM\ExoBundle\Entity\ItemType\AbstractItem;
use UJM\ExoBundle\Library\Attempt\AnswerPartInterface;
use UJM\ExoBundle\Library\Item\ItemType;
use UJM\ExoBundle\Serializer\Item\Type\CodeQuestionSerializer;
use UJM\ExoBundle\Validator\JsonSchema\Attempt\AnswerData\CodeAnswerValidator;
use UJM\ExoBundle\Validator\JsonSchema\Item\Type\CodeQuestionValidator;

/**
 * Code question definition.
 */
class CodeDefinition extends AbstractDefinition
{
    /**
     * @var OpenQuestionValidator
     */
    private $validator;

    /**
     * @var OpenAnswerValidator
     */
    private $answerValidator;

    /**
     * @var OpenQuestionSerializer
     */
    private $serializer;

    /**
     * OpenDefinition constructor.
     *
     * @param OpenQuestionValidator  $validator
     * @param OpenAnswerValidator    $answerValidator
     * @param OpenQuestionSerializer $serializer
     */
    public function __construct(
        CodeQuestionValidator $validator,
        CodeAnswerValidator $answerValidator,
        CodeQuestionSerializer $serializer
    ) {
        $this->validator = $validator;
        $this->answerValidator = $answerValidator;
        $this->serializer = $serializer;
    }

    /**
     * Gets the open question mime-type.
     *
     * @return string
     */
    public static function getMimeType()
    {
        return ItemType::CODE;
    }

    /**
     * Gets the open question entity.
     *
     * @return string
     */
    public static function getEntityClass()
    {
        return '\UJM\ExoBundle\Entity\ItemType\CodeQuestion';
    }

    /**
     * Gets the open question validator.
     *
     * @return CodeQuestionValidator
     */
    protected function getQuestionValidator()
    {
        return $this->validator;
    }

    /**
     * Gets the open answer validator.
     *
     * @return OpenAnswerValidator
     */
    protected function getAnswerValidator()
    {
        return $this->answerValidator;
    }

    /**
     * Gets the open question serializer.
     *
     * @return CodeQuestionSerializer
     */
    protected function getQuestionSerializer()
    {
        return $this->serializer;
    }

    /**
     * Not implemented for code questions as it's not auto corrected.
     *
     * @param AbstractItem $question
     * @param $answer
     *
     * @return bool
     */
    public function correctAnswer(AbstractItem $question, $answer)
    {
        return false;
    }

    /**
     * Not implemented for code questions as it's not auto corrected.
     *
     * @param AbstractItem $question
     *
     * @return AnswerPartInterface[]
     */
    public function expectAnswer(AbstractItem $question)
    {
        return [];
    }

    /**
     * @param AbstractItem $question
     *
     * @return AnswerPartInterface[]
     */
    public function allAnswers(AbstractItem $question)
    {
        return [];
    }

    /**
     * Not implemented because not relevant.
     *
     * @param AbstractItem $openQuestion
     * @param array        $answersData
     * @param int          $total
     *
     * @return array
     */
    public function getStatistics(AbstractItem $openQuestion, array $answersData, $total)
    {
        return [];
    }

    /**
     * No additional identifier to regenerate.
     *
     * @param AbstractItem $item
     */
    public function refreshIdentifiers(AbstractItem $item)
    {
        return;
    }

    public function getCsvAnswers(AbstractItem $item, Answer $answer)
    {
        return [json_decode($answer->getData(), true)];
    }
}
