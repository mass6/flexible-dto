<?php

namespace Mass6\FlexibleDTO\Tests\SampleDTOs;

use Mass6\FlexibleDTO\DataTransferObject;

class IgnoreNonPermittedPropertiesDTO extends DataTransferObject
{
    protected bool $ignoreNonPermittedProperties = true;

    protected function allowedProperties(): array
    {
        return [
            'first_name',
            'last_name',
            'fullName',
            'age',
        ];
    }
}
