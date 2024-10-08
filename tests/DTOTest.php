<?php

namespace Mass6\FlexibleDTO\Tests;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use Mass6\FlexibleDTO\DataTransferObject;
use Mass6\FlexibleDTO\Tests\SampleDTOs\CaseInsensitiveDTO;
use Mass6\FlexibleDTO\Tests\SampleDTOs\DefaultDTO;
use Mass6\FlexibleDTO\Tests\SampleDTOs\IgnoreNonPermittedPropertiesDTO;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DTOTest extends TestCase
{
    #[Test]
    public function it_constructs_a_dto_from_an_array()
    {
        $data = [
            'first_name' => 'Luca',
            'last_name' => 'Brasi',
        ];
        $this->assertInstanceOf(DataTransferObject::class, new DefaultDTO($data));
    }

    #[Test]
    public function it_constructs_a_dto_using_the_static_make_method()
    {
        $data = [
            'first_name' => 'Luca',
            'last_name' => 'Brasi',
        ];
        $this->assertInstanceOf(DataTransferObject::class, DefaultDTO::make($data));
    }

    #[Test]
    public function it_constructs_a_dto_from_parameters()
    {
        $dto = new DefaultDTO('Luca', 'Brasi', 'Luca Brasi', 44);
        $this->assertInstanceOf(DataTransferObject::class, $dto);
        $this->assertEquals([
            'first_name' => 'Luca',
            'last_name' => 'Brasi',
            'fullName' => 'Luca Brasi',
            'age' => 44,
        ], $dto->getAll());
    }

    #[Test]
    public function it_constructs_a_dto_from_a_collection()
    {
        $data = Collection::make([
            'first_name' => 'Luca',
            'last_name' => 'Brasi',
        ]);
        $this->assertInstanceOf(DataTransferObject::class, new DefaultDTO($data));
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
        $dto = new DefaultDTO($data);
        $this->assertEquals($data, $dto->getAll());
    }

    #[Test]
    public function it_throws_an_exception_if_given_a_property_that_is_now_allowed()
    {
        $data = ['middle_name' => 'Bruiser'];

        try {
            new DefaultDTO($data);
            $this->fail('An exception should have been thrown.');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('middle_name is not an allowed property.', $e->getMessage());
        }
    }

    #[Test]
    public function it_returns_null_values_if_properties_is_not_set()
    {
        $data = [
            'first_name' => 'Luca',
            'last_name' => 'Brasi',
        ];
        $dto = new DefaultDTO($data);
        $this->assertEquals([
            'first_name' => 'Luca',
            'last_name' => 'Brasi',
            'fullName' => null,
            'age' => null,
        ], $dto->getAll());
    }

    #[Test]
    public function it_returns_null_values_if_properties_is_not_set_but_allowed_when_using_magic_methods()
    {
        $dto = new DefaultDTO(['first_name' => 'Luca']);
        $this->assertNull($dto->age);
        $this->assertNull($dto->age());
        $this->assertNull($dto->getAge());
    }

    #[Test]
    public function it_returns_true_if_the_given_property_exists()
    {
        $data = [
            'first_name' => 'Luca',
            'last_name' => 'Brasi',
        ];
        $dto = new DefaultDTO($data);
        $this->assertTrue($dto->has('first_name'));
        $this->assertFalse($dto->has('middle_name'));
    }

    #[Test]
    public function it_returns_the_property_value_using_the_get_method()
    {
        $data = ['first_name' => 'Luca'];
        $dto = new DefaultDTO($data);
        $this->assertEquals('Luca', $dto->get('first_name'));
    }

    #[Test]
    public function it_returns_a_default_value_if_the_value_does_not_exist_when_using_the_get_method()
    {
        $data = ['first_name' => 'Luca'];
        $dto = new DefaultDTO($data);
        $this->assertNull($dto->get('last_name'));
        $this->assertEquals('Brasi', $dto->get('last_name', 'Brasi'));
    }

    #[Test]
    public function it_returns_the_property_value_using_the_matching_property_name()
    {
        $data = ['first_name' => 'Luca'];
        $dto = new DefaultDTO($data);
        $this->assertEquals('Luca', $dto->first_name);
    }

    #[Test]
    public function it_returns_the_property_value_using_the_camel_case_property_name()
    {
        $data = ['first_name' => 'Luca'];
        $dto = new DefaultDTO($data);
        $this->assertEquals('Luca', $dto->firstName);
    }

    #[Test]
    public function it_returns_the_property_value_using_the_snake_case_property_name()
    {
        $data = ['fullName' => 'Luca Brasi'];
        $dto = new DefaultDTO($data);
        $this->assertEquals('Luca Brasi', $dto->full_name);
    }

    #[Test]
    public function it_returns_the_property_value_using_a_method()
    {
        $data = ['first_name' => 'Luca'];
        $dto = new DefaultDTO($data);
        $this->assertEquals('Luca', $dto->firstName());
    }

    #[Test]
    public function it_returns_the_property_value_using_a_getter_method()
    {
        $data = ['first_name' => 'Luca'];
        $dto = new DefaultDTO($data);
        $this->assertEquals('Luca', $dto->getFirstName());
    }

    #[Test]
    public function it_ignores_non_permitted_properties()
    {
        $allowed = [
            'first_name' => 'Luca',
            'last_name' => 'Brasi',
            'fullName' => 'Luca Brasi',
            'age' => 35,
        ];
        $nonPermitted = ['middle_name' => 'Carlo'];
        $dto = new IgnoreNonPermittedPropertiesDTO(array_merge($allowed, $nonPermitted));
        $this->assertEquals($allowed, $dto->getAll());
    }

    #[Test]
    public function it_accepts_case_insensitive_properties()
    {
        $data = [
            'firstName' => 'Luca',
            'last_name' => 'Brasi',
            'full_name' => 'Luca Brasi',
            'age' => 35,
        ];
        $dto = new CaseInsensitiveDTO($data);
        $this->assertEquals([
            'first_name' => 'Luca',
            'lastName' => 'Brasi',
            'full name' => 'Luca Brasi',
            'age' => 35,
        ], $dto->getAll());
    }
}
