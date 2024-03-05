<?php

namespace Mass6\FlexibleDTO\Tests\SampleDTOs;

use Mass6\FlexibleDTO\DataTransferObject;

class BlankDTO extends DataTransferObject
{
    protected function allowedProperties(): array
    {
        return ['*'];
    }
}
