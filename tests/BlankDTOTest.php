<?php

namespace Mass6\FlexibleDTO\Tests;

use Mass6\FlexibleDTO\DataTransferObject;
use Mass6\FlexibleDTO\Tests\SampleDTOs\BlankDTO;
use PHPUnit\Framework\TestCase;

class BlankDTOTest extends TestCase
{
    /** @test */
    public function it_constructs_a_dto_from_an_array()
    {
        $data = [
            'first_name' => 'Luca',
            'last_name' => 'Brasi',
        ];
        $this->assertInstanceOf(DataTransferObject::class, new BlankDTO($data));
    }

    /** @test */
    public function it_returns_the_object_data_as_an_array()
    {
        $data = [
            'first_name' => 'Luca',
            'last_name' => 'Brasi',
            'fullName' => 'Luca Brasi',
            'age' => 35,
        ];
        $dto = new BlankDTO($data);
        $this->assertEquals($data, $dto->getAll());
    }

    /** @test */
    public function it_returns_all_set_values()
    {
        $data = [
            'first_name' => 'Luca',
            'last_name' => 'Brasi',
        ];
        $dto = new BlankDTO($data);
        $this->assertEquals([
            'first_name' => 'Luca',
            'last_name' => 'Brasi',
        ], $dto->getAll());
    }
}
