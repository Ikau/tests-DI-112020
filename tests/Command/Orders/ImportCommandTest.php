<?php

namespace App\Tests\Command\Orders;

use App\Entity\Customer;
use App\Entity\Order;
use DateTime;
use Doctrine\ORM\EntityManager;
use Faker\Factory;
use Faker\Generator;
use ImportCommandException;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamFile;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Tester\CommandTester;

class ImportCommandTest extends KernelTestCase
{
    private const ARG_CUSTOMERS_FILEPATH = 'pathToCustomersCsv';
    private const ARG_ORDERS_FILEPATH = 'pathToOrdersCsv';
    private const ROOT_DIR = 'testRoot';

    /**
     * @var array
     */
    private $args;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Generator
     */
    private $faker;

    /**
     * @var vfsStreamDirectory
     */
    private $rootDir;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
        $this->em = self::bootKernel()
            ->getContainer()
            ->get('doctrine')
            ->getManager();

        // Setting virtual filesystem
        $this->rootDir = vfsStream::setup(self::ROOT_DIR);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null;
    }

    /**
     * Test that customers and orders fully defined are well inserted.
     */
    public function testExecuteSuccess(): void
    {
        $numberLines = $this->faker->numberBetween(1, 10);

        $customersCsv = $this->getRandomCsvFile();
        $expectedCustomersData = $this->getFakeCustomerData($numberLines);
        $customersCsv->setContent($this->formatArrayToParsableData($expectedCustomersData));

        $ordersCsv = $this->getRandomCsvFile();
        $expectedOrdersData = $this->getFakeOrderData($numberLines);
        $ordersCsv->setContent($this->formatArrayToParsableData($expectedOrdersData));

        $this->args = [
            self::ARG_CUSTOMERS_FILEPATH => $customersCsv->url(),
            self::ARG_ORDERS_FILEPATH => $ordersCsv->url()
        ];
        $this->executeCommand();

        // Checking new data in database
        for ($i=0; $i<$numberLines; $i++)
        {
            $customerTest = $this->em->getRepository(Customer::class)
                ->findOneBy([
                    'id' => (int)$expectedCustomersData[$i][0],
                    'title' => $expectedCustomersData[$i][1],
                    'lastname' => $expectedCustomersData[$i][2],
                    'firstname' => $expectedCustomersData[$i][3],
                    'postal_code' => $expectedCustomersData[$i][4],
                    'city' => $expectedCustomersData[$i][5],
                    'email' => $expectedCustomersData[$i][6]
                ]);
            $this->assertNotNull($customerTest);

            $orderTest = $this->em->getRepository(Order::class)
                ->findOneBy([
                    'purchase_identifier' => $expectedOrdersData[$i][0],
                    // Not testing customer_id because customers were already tested
                    'product_id' => $expectedOrdersData[$i][2],
                    'quantity' => $expectedOrdersData[$i][3],
                    'price' => $expectedOrdersData[$i][4],
                    'currency' => $expectedOrdersData[$i][5],
                    'date' => DateTime::createFromFormat('Y-m-d', $expectedOrdersData[$i][6])
                ]);
            $this->assertNotNull($orderTest);
        }
    }

    /**
     * Test that a customer with NULL 'lastname', 'firstname', 'postal_code', 'city' and 'email' is inserted.
     */
    public function testExecuteSuccessWithNullData()
    {
        $numberLines = 1;
        $expectedCustomerId = $this->faker->numberBetween(2, 100);
        $expectedCustomerTitle = $this->faker->numberBetween(1, 2);

        $customersCsv = $this->getRandomCsvFile();
        $customersData = $this->getFakeCustomerData($numberLines);
        $customersCsv->setContent(
            $this->formatArrayToParsableData($customersData)
            // Adding a customer with NULL data
            . "{$expectedCustomerId};{$expectedCustomerTitle};\n"
        );

        $ordersCsv = $this->getRandomCsvFile();
        $ordersData = $this->getFakeOrderData($numberLines);
        $ordersCsv->setContent($this->formatArrayToParsableData($ordersData));

        $this->args = [
            self::ARG_CUSTOMERS_FILEPATH => $customersCsv->url(),
            self::ARG_ORDERS_FILEPATH => $ordersCsv->url()
        ];
        $this->executeCommand();

        // Checking the customer with null data was inserted
        $customer = $this->em->getRepository(Customer::class)
            ->findOneBy([
                'id' => (int)$expectedCustomerId,
                'title' => $expectedCustomerTitle
            ]);
        $this->assertNotNull($customer);
    }

    /**
     * Test that a duplicate customer (by id) is not imported
     */
    public function testExecuteSuccessWithDuplicateCustomers(): void
    {
        $numberLines = 2;

        $customersCsv = $this->getRandomCsvFile();
        $customersData = $this->getFakeCustomerData($numberLines);
        $customersCsv->setContent(
            $this->formatArrayToParsableData($customersData)
            // Duplicate the first customer
            . "1;{$this->faker->numberBetween(1,2)}\n"
        );

        $ordersCsv = $this->getRandomCsvFile();
        $ordersData = $this->getFakeOrderData($numberLines);
        $ordersCsv->setContent($this->formatArrayToParsableData($ordersData));

        $this->args = [
            self::ARG_CUSTOMERS_FILEPATH => $customersCsv->url(),
            self::ARG_ORDERS_FILEPATH => $ordersCsv->url()
        ];
        $output = $this->executeCommand();

        $expectedMessage = 'a customer with the same id already exists';
        $this->assertStringContainsString($expectedMessage, $output->getDisplay());
    }

    /**
     * Test that a duplicate order (by id) is not imported
     */
    public function testExecuteSuccessWithDuplicateOrders()
    {
        $numberLines = 2;

        $customersCsv = $this->getRandomCsvFile();
        $customersData = $this->getFakeCustomerData($numberLines);
        $customersCsv->setContent($this->formatArrayToParsableData($customersData));

        $duplicateOrder = 'identifier1;'
            . "{$this->faker->randomNumber()};"
            . "{$this->faker->randomNumber()};"
            . "{$this->faker->randomNumber()};"
            . "{$this->faker->randomFloat()};"
            . "{$this->faker->randomElement(['euros', 'dollars'])};"
            . "{$this->faker->date('Y-m-d')};\n";

        $ordersCsv = $this->getRandomCsvFile();
        $ordersData = $this->getFakeOrderData($numberLines);
        $ordersCsv->setContent(
            $this->formatArrayToParsableData($ordersData)
            . $duplicateOrder
        );

        $this->args = [
            self::ARG_CUSTOMERS_FILEPATH => $customersCsv->url(),
            self::ARG_ORDERS_FILEPATH => $ordersCsv->url()
        ];
        $output = $this->executeCommand();

        $expectedMessage = 'order with the same identifier already exists.';
        $this->assertStringContainsString($expectedMessage, $output->getDisplay());
    }

    /**
     * Test that an order without an existing customer is not imported
     */
    public function testExecuteSuccessWithOrdersWithoutCustomer()
    {
        $numberLines = 2;

        $customersCsv = $this->getRandomCsvFile();
        $customersData = $this->getFakeCustomerData($numberLines);
        $customersCsv->setContent($this->formatArrayToParsableData($customersData));

        $duplicateOrder = "identifier{$this->faker->numberBetween(3, 100)};"
            . "{$this->faker->randomNumber()};"
            . "{$this->faker->randomNumber(2)};"
            . "{$this->faker->randomNumber()};"
            . "{$this->faker->randomFloat()};"
            . "{$this->faker->randomElement(['euros', 'dollars'])};"
            . "{$this->faker->date('Y-m-d')};\n";

        $ordersCsv = $this->getRandomCsvFile();
        $ordersData = $this->getFakeOrderData($numberLines);
        $ordersCsv->setContent(
            $this->formatArrayToParsableData($ordersData)
            . $duplicateOrder
        );

        $this->args = [
            self::ARG_CUSTOMERS_FILEPATH => $customersCsv->url(),
            self::ARG_ORDERS_FILEPATH => $ordersCsv->url()
        ];
        $output = $this->executeCommand();

        $expectedMessage = 'the customer was not found.';
        $this->assertStringContainsString($expectedMessage, $output->getDisplay());
    }

    public function testExecuteFailMissingCustomersFilepath(): void
    {
        $this->expectException(RuntimeException::class);

        $this->args = [];
        $this->executeCommand();
    }

    public function testExecuteFailMissingOrdersFilepath(): void
    {
        $this->expectException(RuntimeException::class);

        $this->args = [
            self::ARG_CUSTOMERS_FILEPATH => $this->faker->word
        ];
        $this->executeCommand();
    }

    public function testExecuteFailCustomersCsvNotFound(): void
    {
        $this->expectException(ImportCommandException::class);
        $this->expectExceptionCode(ImportCommandException::ERROR_CODE_FILE_NOT_FOUND_CUSTOMER);

        $this->args = [
            self::ARG_CUSTOMERS_FILEPATH => $this->faker->word,
            self::ARG_ORDERS_FILEPATH => $this->faker->word
        ];
        $this->executeCommand();
    }

    public function testExecuteFailOrdersCsvNotFound(): void
    {
        $this->expectException(ImportCommandException::class);
        $this->expectExceptionCode(ImportCommandException::ERROR_CODE_FILE_NOT_FOUND_ORDER);

        $customerMockCsv = $this->getRandomCsvFile();
        $this->args = [
            self::ARG_CUSTOMERS_FILEPATH => $customerMockCsv->url(),
            self::ARG_ORDERS_FILEPATH => $this->faker->word
        ];
        $this->executeCommand();
    }

    public function testExecuteFailOrdersCsvNotFile(): void
    {
        $this->expectException(ImportCommandException::class);
        $this->expectExceptionCode(ImportCommandException::ERROR_CODE_FILE_NOT_FOUND_ORDER);

        $customerMockCsv = $this->getRandomCsvFile();
        $this->args = [
            self::ARG_CUSTOMERS_FILEPATH => $customerMockCsv->url(),
            self::ARG_ORDERS_FILEPATH => $this->rootDir->url()
        ];
        $this->executeCommand();
    }

    public function testExecuteFailCustomersCsvNotFile(): void
    {
        $this->expectException(ImportCommandException::class);
        $this->expectExceptionCode(ImportCommandException::ERROR_CODE_FILE_NOT_FOUND_CUSTOMER);

        $this->args = [
            self::ARG_CUSTOMERS_FILEPATH => $this->rootDir->url(),
            self::ARG_ORDERS_FILEPATH => $this->faker->word
        ];
        $this->executeCommand();
    }

    /* TODO: Did not succeed in mocking a fopen failure
    public function testExecuteFailCustomersCsvNotOpened(): void
    {
    }
    */

    /* TODO: Did not succeed in mocking a fopen failure
    public function testExecuteFailOrdersCsvNotOpened(): void
    {
    }
    */

    /**
     * Help function to ease tests.
     * @return CommandTester
     */
    private function executeCommand(): CommandTester
    {
        $kernel = static::createKernel();
        $application = new Application($kernel);

        $command = $application->find('ugo:orders:import');
        $commandTester = new CommandTester($command);
        $commandTester->execute($this->args);

        return $commandTester;
    }

    /**
     * Helper function to create a random CSV file at $rootDir.
     * @return vfsStreamFile
     */
    private function getRandomCsvFile(): vfsStreamFile
    {
        return vfsStream::newFile("{$this->faker->word}.csv")->at($this->rootDir);
    }

    /**
     * Create 'numberLines' new customers with random data.
     * @param int $numberLines
     * @return array
     */
    private function getFakeCustomerData(int $numberLines): array
    {
        $content = [];
        for ($i=1; $i<=$numberLines; $i++) {
            $newCustomer = [];
            $newCustomer[] = $i;
            $newCustomer[] = $this->faker->numberBetween(1, 2);
            $newCustomer[] = $this->faker->lastName;
            $newCustomer[] = $this->faker->firstName;
            $newCustomer[] = $this->faker->postcode;
            $newCustomer[] = $this->faker->city;
            $newCustomer[] = $this->faker->email;

            $content[] = $newCustomer;
        }
        return $content;
    }

    /**
     * Create 'numberLines' new orders, assuming 'numberLines' customers existing.
     * @param int $numberLines
     * @return array
     */
    private function getFakeOrderData(int $numberLines): array
    {
        $content = [];
        for ($i=1; $i<=$numberLines; $i++)
        {
            $newOrder = [];
            $newOrder[] = "identifier{$i}";
            $newOrder[] = $i;
            $newOrder[] = $this->faker->randomNumber();
            $newOrder[] = $this->faker->randomNumber();
            $newOrder[] = $this->faker->randomFloat();
            $newOrder[] = $this->faker->randomElement(['euros', 'dollars']);
            $newOrder[] = $this->faker->date('Y-m-d'); // Date is automatically parsed in this format

            $content[] = $newOrder;
        }
        return $content;
    }

    /**
     * Return a parsable CSV content from a fake data.
     * @param array $data
     * @return string
     */
    private function formatArrayToParsableData(array $data): string
    {
        $parsableData = "header line\n";
        foreach ($data as $currentLine) {
            $parsableData = $parsableData . implode(';', $currentLine) . "\n";
        }
        return $parsableData;
    }
}