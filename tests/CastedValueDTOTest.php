<?php

namespace Mass6\FlexibleDTO\Tests;

use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use Mass6\FlexibleDTO\Tests\SampleDTOs\CastedValueDTO;
use PHPUnit\Framework\TestCase;

class CastedValueDTOTest extends TestCase
{
    /** @test */
    public function it_can_cast_a_property_value_as_a_boolean()
    {
        $data = ['released' => '0'];
        $dto = new CastedValueDTO($data);
        $this->assertEquals('boolean', gettype($dto->released));
        $this->assertFalse($dto->released);
    }

    /** @test */
    public function it_can_cast_a_valid_property_value_date_to_a_carbon_object()
    {
        $data = ['released_on' => '1972-12-26'];
        $dto = new CastedValueDTO($data);
        $this->assertInstanceOf(Carbon::class, $dto->releasedOn());
    }

    /** @test */
    public function it_throws_an_exception_if_the_property_value_date_format_is_invalid()
    {
        $data = ['released_on' => 'invalid_date'];
        try {
            $dto = new CastedValueDTO($data);
            $this->fail('DTO was constructed even though the date format is invalid.');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('The provided released_on value of `invalid_date` is not a valid date format.', $e->getMessage());
        }
    }

    /** @test */
    public function it_can_cast_a_property_value_to_an_illuminate_collection()
    {
        $data = [
            'actors' => [
                'Marlon Brando',
                'Al Pacino',
            ],
        ];
        $dto = new CastedValueDTO($data);
        $this->assertEquals(Collection::class, get_class($dto->actors));
        $this->assertEquals([
            'Marlon Brando',
            'Al Pacino',
        ], $dto->actors()->all());
    }

    /** @test */
    public function it_can_cast_an_object_property_value_to_an_array()
    {
        $characters  = [
            'Don Corleone',
            'Michael Corleone',
        ];
        $data = ['characters' => (object) $characters];
        $this->assertEquals('object', gettype($data['characters']));

        $dto = new CastedValueDTO($data);
        $this->assertEquals('array', gettype($dto->characters));
        $this->assertEquals($characters, $dto->characters);
    }

    /** @test */
    public function it_can_cast_a_property_value_to_a_integer()
    {
        $data = ['oscars' => '3'];
        $dto = new CastedValueDTO($data);
        $this->assertEquals('integer', gettype($dto->oscars));
        $this->assertEquals(3, $dto->oscars);
    }

    /** @test */
    public function it_can_cast_a_property_value_to_a_string()
    {
        $data = ['dvd_year' => 1998];
        $dto = new CastedValueDTO($data);
        $this->assertEquals('string', gettype($dto->dvd_year));
        $this->assertEquals('1998', $dto->dvd_year);
    }

    /** @test */
    public function it_throws_an_exception_if_the_property_value_cannot_be_cast_to_a_string()
    {
        $data = ['dvd_year' => new DateTime()];
        try {
            $dto = new CastedValueDTO($data);
            $this->fail('DTO was constructed even though the the dvd_year property could not be cast to string.');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('The provided dvd_year value could not be cast to string.', $e->getMessage());
        }
    }

    /** @test */
    public function it_can_cast_a_property_value_using_a_custom_cast_object()
    {
        $data = ['title' => 'The Godfather'];
        $dto = new CastedValueDTO($data);
        $this->assertEquals('THE GODFATHER', $dto->title);
    }
}
