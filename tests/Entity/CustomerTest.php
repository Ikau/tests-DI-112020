<?php

namespace App\Tests\Entity;

use App\Entity\Customer;
use Faker\Factory;

class CustomerTest extends \PHPUnit\Framework\TestCase
{
    public function testGetTitleNameMme(): void
    {
        $customer = new Customer();
        $customer->setTitle(1);
        $this->assertEquals('mme', $customer->getTitleName());
    }

    public function testGetTitleNameM(): void
    {
        $customer = new Customer();
        $customer->setTitle(2);
        $this->assertEquals('m', $customer->getTitleName());
    }

    public function testToAssociativeArray(): void
    {
        $faker = Factory::create();

        $expectedValues = [
            $faker->randomNumber(),
            $faker->numberBetween(1, 2),
            $faker->lastName,
            $faker->firstName,
            $faker->postcode,
            $faker->city,
            $faker->email
        ];

        $customer = new Customer();
        $customer->setId($expectedValues[0])
            ->setTitle($expectedValues[1])
            ->setLastname($expectedValues[2])
            ->setFirstname($expectedValues[3])
            ->setPostalCode($expectedValues[4])
            ->setCity($expectedValues[5])
            ->setEmail($expectedValues[6]);

        $actualValues = $customer->toAssociativeArray();

        $this->assertEquals($expectedValues[0], $actualValues[Customer::COLUMN_NAME_ID]);
        $this->assertEquals($expectedValues[1] === 1 ? 'mme' : 'm', $actualValues[Customer::COLUMN_NAME_TITLE]);
        $this->assertEquals($expectedValues[2], $actualValues[Customer::COLUMN_NAME_LAST_NAME]);
        $this->assertEquals($expectedValues[3], $actualValues[Customer::COLUMN_NAME_FIRST_NAME]);
        $this->assertEquals($expectedValues[4], $actualValues[Customer::COLUMN_NAME_POSTAL_CODE]);
        $this->assertEquals($expectedValues[5], $actualValues[Customer::COLUMN_NAME_CITY]);
        $this->assertEquals($expectedValues[6], $actualValues[Customer::COLUMN_NAME_EMAIL]);
    }

    public function testToAssociativeArrayWithNullValue(): void
    {
        $faker = Factory::create();

        $expectedId = $faker->randomNumber();
        $expectedTitle = $faker->numberBetween(1, 2);

        $customer = new Customer();
        $customer->setId($expectedId)
            ->setTitle($expectedTitle)
            ->setLastname('')
            ->setFirstname('')
            ->setPostalCode('')
            ->setCity('')
            ->setEmail('');

        $actualValues = $customer->toAssociativeArray();

        $this->assertEquals($expectedId, $actualValues[Customer::COLUMN_NAME_ID]);
        $this->assertEquals($expectedTitle === 1 ? 'mme' : 'm', $actualValues[Customer::COLUMN_NAME_TITLE]);
        $this->assertEquals('', $actualValues[Customer::COLUMN_NAME_LAST_NAME]);
        $this->assertEquals('', $actualValues[Customer::COLUMN_NAME_FIRST_NAME]);
        $this->assertEquals('', $actualValues[Customer::COLUMN_NAME_POSTAL_CODE]);
        $this->assertEquals('', $actualValues[Customer::COLUMN_NAME_CITY]);
        $this->assertEquals('', $actualValues[Customer::COLUMN_NAME_EMAIL]);
    }


}