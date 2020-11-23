<?php

namespace App\Tests\Entity;

use App\Entity\Customer;
use App\Entity\Order;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class OrderTest extends TestCase
{
    public function testToAssociativeArray(): void
    {
        $faker = Factory::create();

        $expectedValues = [
            "identifier{$faker->randomNumber()}",
            $faker->randomElement(['euros', 'dollars']),
            $faker->randomNumber(),
            $faker->date('Y-m-d'),
            $faker->randomFloat(),
            $faker->randomNumber(),
            $faker->randomNumber()
        ];

        $customer = new Customer();
        $customer->setId($expectedValues[2])
            ->setTitle($faker->numberBetween(1, 2));

        $order = new Order();
        $order->setPurchaseIdentifier($expectedValues[0])
            ->setCurrency($expectedValues[1])
            ->setCustomer($customer)
            ->setDate(\DateTime::createFromFormat('Y-m-d', $expectedValues[3]))
            ->setPrice($expectedValues[4])
            ->setProductId($expectedValues[5])
            ->setQuantity($expectedValues[6]);

        $actualValues = $order->toAssociativeArray();

        $this->assertEquals($expectedValues[0], $actualValues[Order::COLUMN_NAME_ID]);
        $this->assertEquals($expectedValues[1], $actualValues[Order::COLUMN_NAME_CURRENCY]);
        $this->assertEquals($expectedValues[2], $actualValues[Order::COLUMN_NAME_CUSTOMER_ID]);
        $this->assertEquals($expectedValues[3], $actualValues[Order::COLUMN_NAME_DATE]);
        $this->assertEquals($expectedValues[4], $actualValues[Order::COLUMN_NAME_PRICE]);
        $this->assertEquals($expectedValues[5], $actualValues[Order::COLUMN_NAME_PRODUCT_ID]);
        $this->assertEquals($expectedValues[6], $actualValues[Order::COLUMN_NAME_QUANTITY]);
    }
}