<?php

namespace Mass6\FlexibleDTO\Tests;

use Mass6\FlexibleDTO\DataTransferObject;
use Mass6\FlexibleDTO\Tests\SampleDTOs\BlankDTO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BlankDTOTest extends TestCase
{
    #[Test]
    public function it_constructs_a_dto_from_an_array()
    {
        $data = [
            'first_name' => 'Luca',
            'last_name' => 'Brasi',
        ];
        $this->assertInstanceOf(DataTransferObject::class, new BlankDTO($data));
    }

    #[Test]
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

    #[Test]
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

    #[Test]
    public function it_returns_a_default_value_if_the_value_does_not_exist_when_using_the_get_method()
    {
        $data = ['first_name' => 'Luca'];
        $dto = new BlankDTO($data);
        $this->assertNull($dto->get('last_name'));
        $this->assertEquals('Brasi', $dto->get('last_name', 'Brasi'));
    }
}
