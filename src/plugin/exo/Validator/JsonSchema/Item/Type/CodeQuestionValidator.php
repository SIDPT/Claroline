<?php

namespace UJM\ExoBundle\Validator\JsonSchema\Item\Type;

use UJM\ExoBundle\Library\Validator\JsonSchemaValidator;

class CodeQuestionValidator extends JsonSchemaValidator
{
    public function getJsonSchemaUri()
    {
        return 'question/code/schema.json';
    }

    public function validateAfterSchema($question, array $options = [])
    {
        return [];
    }
}
