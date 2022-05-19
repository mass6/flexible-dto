<?php

namespace Mass6\FlexibleDTO\Tests\SampleDTOs;

use Mass6\FlexibleDTO\DataTransferObject;

class CaseInsensitiveDTO extends DataTransferObject
{
    protected bool $caseSensitive = false;
    protected function allowedProperties(): array
    {
        return [
            'first_name',
            'lastName',
            'full name',
            'age'
        ];
    }
}
