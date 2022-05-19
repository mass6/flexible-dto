<?php

namespace Mass6\FlexibleDTO\Tests\SampleDTOs;

use Mass6\FlexibleDTO\Validation\ValidatesProperties;
use Mass6\FlexibleDTO\DataTransferObject;

class ValidatedDTO extends DataTransferObject
{
    use ValidatesProperties;

    protected function allowedProperties(): array
    {
        return [
            'title',
            'released',
        ];
    }

    public function getRules(): array
    {
        return [
            'title' => 'required'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->title === 'Fight Club') {
                $validator->errors()->add('title', 'The first rule of Fight Club is: You do not talk about Fight Club.');
            }
        });
    }

}
