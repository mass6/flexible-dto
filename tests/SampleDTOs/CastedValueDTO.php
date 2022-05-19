<?php

namespace Mass6\FlexibleDTO\Tests\SampleDTOs;

use Mass6\FlexibleDTO\DataTransferObject;
use Mass6\FlexibleDTO\Tests\SampleDTOs\Casts\Uppercase;

class CastedValueDTO extends DataTransferObject
{
    protected array $casts = [
        'released' => 'boolean',
        'released_on' => 'date',
        'actors' => 'collection',
        'characters' => 'array',
        'oscars' => 'int',
        'dvd_year' => 'string',
        'title' => Uppercase::class,
    ];

    protected function allowedProperties(): array
    {
        return [
            'released',
            'released_on',
            'actors',
            'characters',
            'oscars',
            'dvd_year',
            'title',
        ];
    }
}
