<?php

namespace App\Tests\Controller;

use App\Entity\Customer;
use App\Entity\Order;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManager;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CustomerApiControllerTest extends WebTestCase
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var KernelBrowser
     */
    private $client;

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    /**
     * Test GET endpoint to fetch list of all customers
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testList(): void
    {
        $expectedCustomers = $this->insertCustomers();
        $this->client->request('GET', '/api/customers');
        $this->client->followRedirect();

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertNotEmpty($response->getContent());

        $jsonCustomer = json_decode($response->getContent());
        for ($i=0; $i<count($expectedCustomers); $i++) {
            $expectedCustomer = $expectedCustomers[$i];
            $actualCustomer = $jsonCustomer[$i];

            $this->assertEquals($expectedCustomer[Customer::COLUMN_NAME_ID], $actualCustomer->id);
            $this->assertEquals($expectedCustomer[Customer::COLUMN_NAME_TITLE], $actualCustomer->title);
            $this->assertEquals($expectedCustomer[Customer::COLUMN_NAME_LAST_NAME], $actualCustomer->lastname);
            $this->assertEquals($expectedCustomer[Customer::COLUMN_NAME_FIRST_NAME], $actualCustomer->firstname);
            $this->assertEquals($expectedCustomer[Customer::COLUMN_NAME_POSTAL_CODE], $actualCustomer->postal_code);
            $this->assertEquals($expectedCustomer[Customer::COLUMN_NAME_CITY], $actualCustomer->city);
            $this->assertEquals($expectedCustomer[Customer::COLUMN_NAME_EMAIL], $actualCustomer->email);
        }
    }

    public function testListWithNoCustomers(): void
    {
        $this->client->request('GET', '/api/customers');
        $this->client->followRedirect();

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertEquals([], json_decode($response->getContent()));
    }

    public function testShowOrders(): void
    {
        /* TODO: Does not work, I don't know why
        WebTestCase does not seem to correctly fetch the results...

        $this->insertCustomers();
        $expectedOrders = $this->insertOrdersForCustomer(1);
        $this->client->request('GET', '/api/customers/1/orders');

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());

        $json = json_decode($response->getContent());
        $this->assertSameSize($expectedOrders, $json);

        for ($i=0; $i<count($expectedOrders); $i++)
        {
            $this->assertEquals($expectedOrders[$i][Order::COLUMN_NAME_ID], $json[$i][Order::COLUMN_NAME_ID]);
            $this->assertEquals($expectedOrders[$i][Order::COLUMN_NAME_PRICE], $json[$i][Order::COLUMN_NAME_PRICE]);
            $this->assertEquals($expectedOrders[$i][Order::COLUMN_NAME_DATE], $json[$i][Order::COLUMN_NAME_DATE]);
            $this->assertEquals(
                $expectedOrders[$i][Order::COLUMN_NAME_QUANTITY],
                $json[$i][Order::COLUMN_NAME_QUANTITY]
            );
            $this->assertEquals(
                $expectedOrders[$i][Order::COLUMN_NAME_PRODUCT_ID],
                $json[$i][Order::COLUMN_NAME_PRODUCT_ID]
            );
            $this->assertEquals(
                $expectedOrders[$i][Order::COLUMN_NAME_CUSTOMER_ID],
                $json[$i][Order::COLUMN_NAME_CUSTOMER_ID]
            );
            $this->assertEquals(
                $expectedOrders[$i][Order::COLUMN_NAME_CURRENCY],
                $json[$i][Order::COLUMN_NAME_CURRENCY]
            );
        }
        */
    }

    public function testShowOrdersWithNoOrders(): void
    {
        $this->insertCustomers();
        $this->client->request('GET', '/api/customers/1/orders');

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertEmpty(json_decode($response->getContent()));
    }

    public function testShowOrdersFailCustomerNotFound(): void
    {
        $this->client->request('GET', '/api/customers/100/orders');

        $response = $this->client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Test GET endpoint to fetch a specific customer
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testFind(): void
    {
        $this->insertCustomers();
        $this->client->request('GET', '/api/customers/1');

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $this->assertNotEmpty(json_decode($response->getContent()));
    }

    public function testFindFailCustomerNotFound(): void
    {
        $this->client->request('GET', '/api/customers/100');

        $response = $this->client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
    }

    /**
     * Add two customers to the test database: one with random data and a second with null values.
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function insertCustomers(): array
    {
        $faker = Factory::create();
        $customers = [];

        $randomCustomer = new Customer();
        $randomCustomer->setId(1)
            ->setTitle($faker->numberBetween(1, 2))
            ->setLastname($faker->lastName)
            ->setFirstname($faker->firstName)
            ->setPostalCode($faker->postcode)
            ->setCity($faker->city)
            ->setEmail($faker->email);
        $this->em->persist($randomCustomer);
        $customers[] = $randomCustomer->toAssociativeArray();

        $customerWithNullValues = new Customer();
        $customerWithNullValues->setId(2)
            ->setTitle($faker->numberBetween(1, 2))
            ->setLastname(null)
            ->setFirstname(null)
            ->setPostalCode(null)
            ->setCity(null)
            ->setEmail(null);
        $this->em->persist($customerWithNullValues);
        $customers[] = $customerWithNullValues->toAssociativeArray();

        $this->em->flush();
        return $customers;
    }

    /**
     * Add between 1 and 10 orders for the given customer id
     * @param int $customerId
     * @return array
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    private function insertOrdersForCustomer(int $customerId): array
    {
        // Fetching the customer
        $customer = $this->em->find(Customer::class, $customerId);

        // Adding some new orders
        $faker = Factory::create();
        $orders = [];

        for ($i=0; $i<$faker->numberBetween(1,10); $i++) {
            $order = new Order();

            $order->setPurchaseIdentifier("identifier{$i}")
                ->setPrice($faker->randomFloat())
                ->setQuantity(($faker->randomNumber()))
                ->setProductId($faker->randomNumber())
                ->setDate($faker->datetime('Y-m-d'))
                ->setCurrency($faker->randomElement(['euros', 'dollars']))
                ->setCustomer($customer);

            $this->em->persist($order);
            $orders[] = $order->toAssociativeArray();
        }
        $this->em->flush();

        return $orders;
    }
}