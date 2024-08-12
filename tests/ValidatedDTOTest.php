<?php

namespace Mass6\FlexibleDTO\Tests;

use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Mass6\FlexibleDTO\Tests\SampleDTOs\ValidatedDTO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ValidatedDTOTest extends TestCase
{
    #[Test]
    public function it_validates_properties()
    {
        $data = Collection::make(['title' => '']);

        try {
            new ValidatedDTO($data);
            $this->fail('DTO was constructed even though the required field title property was empty.');
        } catch (ValidationException $e) {
            $this->assertEquals('The title property is required.', $e->validator->errors()->first());
        }
    }

    #[Test]
    public function it_runs_after_validation_hooks()
    {
        $data = Collection::make(['title' => 'Fight Club']);

        try {
            new ValidatedDTO($data);
            $this->fail('DTO was constructed even though the required field title property is Fight Club.');
        } catch (ValidationException $e) {
            $this->assertEquals('The first rule of Fight Club is: You do not talk about Fight Club.', $e->validator->errors()->first());
        }
    }
}
