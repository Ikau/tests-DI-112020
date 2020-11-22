<?php

namespace App\Tests\Entity;

use App\Entity\Customer;

class CustomerTest extends \PHPUnit\Framework\TestCase
{
    public function testGetTitleNameMme()
    {
        $customer = new Customer();
        $customer->setTitle(1);
        $this->assertEquals('mme', $customer->getTitleName());
    }

    public function testGetTitleNameM()
    {
        $customer = new Customer();
        $customer->setTitle(2);
        $this->assertEquals('m', $customer->getTitleName());
    }


}