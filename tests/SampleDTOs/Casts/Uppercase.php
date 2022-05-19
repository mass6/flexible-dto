<?php

namespace Mass6\FlexibleDTO\Tests\SampleDTOs\Casts;

use Illuminate\Support\Str;
use Mass6\FlexibleDTO\Casts\CastsProperties;

class Uppercase implements CastsProperties
{
    public function cast($value)
    {
        return Str::upper($value);
    }
}
