<?php

namespace App\Command\Orders;

use App\Entity\Customer;
use App\Entity\Order;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Allows user to import a list of customers and orders from CSV files
 *
 * Class ImportCommand
 * @package App\Command\Orders
 */
class ImportCommand extends Command
{
    const RETURN_STATUS_OK = 0;

    protected static $defaultName = 'ugo:orders:import';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, string $name = null)
    {
        $this->em = $entityManager;
        $this->logger = $logger;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Import new customers and orders into existing database.')
            ->setHelp(
                'The <info>ugo:orders:import</info> command allows you to '
                . "insert new data for customers and orders into an existing SQLite database.\n"
                . "You will need to specify two CSV file containing the data as arguments.\n\n"
                . "This command assumes that the CSV to import have the following structures:\n"
                . "For <info>customers</info> CSV:\n- First line is empty or headers\n- delimiter ';'\n"
                . "- Data line structure: id, title, lastname, firstname, postal_code, city, email\n"
                . "For <info>orders</info> CSV:\n- First line is empty or headers\n- delimiter ';'\n"
                . "- Data line structure: purchase_identifier, customer_id, product_id, quantity, price, currency, date"
            )
            ->addArgument(
                'pathToCustomersCsv',
                InputArgument::REQUIRED,
                'Relative or absolute filepath to customers data source in CSV format.'
            )
            ->addArgument(
              'pathToOrdersCsv',
                InputArgument::REQUIRED,
                'Relative or absolute filepath to orders data source in CSV format.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Check import file for customers
        $customerCsvFilepath = $input->getArgument('pathToCustomersCsv');
        if (!$this->isPathValid($customerCsvFilepath)) {
            throw new ImportCommandException(
                "File not found for relative or absolute path to '{$customerCsvFilepath}'",
                ImportCommandException::ERROR_CODE_FILE_NOT_FOUND_CUSTOMER
            );
        }

        // Check import file for orders
        $orderCsvFilepath = $input->getArgument('pathToOrdersCsv');
        if (!$this->isPathValid($orderCsvFilepath)) {
            throw new ImportCommandException(
                "File not found for relative or absolute path to '{$orderCsvFilepath}'",
                ImportCommandException::ERROR_CODE_FILE_NOT_FOUND_ORDER
            );
        }

        // Import logic within a transaction
        try {
            $this->em->beginTransaction();

            $this->importCustomers($output, $customerCsvFilepath);
            $this->importOrders($output, $orderCsvFilepath);

            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }

        $output->writeln('<info>Import ended successfully!</info>');
        return self::RETURN_STATUS_OK;
    }

    /**
     * @param string $filepath Relative or absolute filepath to check for existence.
     * @return bool If a file exist in the given path.
     */
    private function isPathValid(string $filepath) : bool
    {
        // Check for relative path first
        if (is_file("./{$filepath}")) {
            return true;
        }

        // Check for absolute path
        return is_file($filepath);
    }

    /**
     * Import new customers data from file located at 'filepath'.
     *
     * Logic expects CSV file with:
     * - first line headers
     * - delimiter ';'
     * - data line: id, title, lastname, firstname, postal_code, city, email
     *
     * @param OutputInterface $output
     * @param string $filepath Path to the CSV file to import customers from.
     * @throws ImportCommandException Could not open the file.
     */
    private function importCustomers(OutputInterface $output, string $filepath)
    {
        // Opening the file to import customers from
        $handle = fopen($filepath, 'r');
        if ($handle === FALSE) {
            throw new ImportCommandException(
                "File {$filepath} could not be opened",
                ImportCommandException::ERROR_CODE_CANNOT_OPEN_FILE_CUSTOMER
            );
        }

        $output->writeln('<info>Importing customers... </info>');
        try {
            // Skipping first line, expected to be headers
            fgetcsv($handle, 0, ';');

            $lineNumber = 1;
            while (($data = fgetcsv($handle, 0, ';')) !== FALSE) {
                // Insert new customer if not a duplicate
                $customerId = (int) $data[0];
                if ($this->customerExists($customerId)) {
                    $output->writeln("<comment> Line {$lineNumber} was not imported "
                        . 'because a customer with the same id already exists</comment>'
                    );
                } else {
                    $this->persistNewCustomer($data);
                }

                $lineNumber++;
            }

            $this->em->flush();
        } finally {
            fclose($handle);
        }
    }

    /**
     * Import new orders data from CSV file located at 'filepath' if related customer exists.
     *
     * Logic expects CSV file with:
     * - first line headers
     * - delimiter ';'
     * - data line: purchase_identifier, customer_id, product_id, quantity, price, currency, date
     *
     * @param OutputInterface $output
     * @param string $filepath Path to the CSV file to import orders from.
     * @throws ImportCommandException Could not open file to import from.
     */
    private function importOrders(OutputInterface $output, string $filepath)
    {
        // Opening the file to import from
        $handle = fopen($filepath, 'r');
        if ($handle === FALSE) {
            throw new ImportCommandException(
                "File {$filepath} could not be opened",
                ImportCommandException::ERROR_CODE_CANNOT_OPEN_FILE_CUSTOMER
            );
        }

        $output->writeln('<info>Importing orders... </info>');
        try {
            // Skipping the first line, expected to be headers
            fgetcsv($handle, 0, ';');

            // Inserting a new order if not a duplicate and customer exists
            $lineNumber = 1;
            while (($data = fgetcsv($handle, 0, ';')) !== FALSE) {
                $orderId = $data[0];
                $customerId = (int)$data[1];

                if ($this->orderExists($orderId)) {
                    $output->writeln("<comment> Line {$lineNumber} was not imported "
                        . 'because an order with the same identifier already exists.'
                    );
                } else if (!$this->customerExists($customerId)) {
                    $output->writeln("<comment> Line {$lineNumber} was not imported "
                        . 'because the customer was not found.'
                    );
                } else {
                    $this->persistNewOrder($data);
                }

                $lineNumber++;
            }

            $this->em->flush();
        } finally {
            fclose($handle);
        }
    }

    /**
     * @param int $customerId
     * @return bool If the customer identified by 'customerId' is already in the database.
     */
    private function customerExists(int $customerId)
    {
        return $this->em->find(Customer::class, $customerId) !== null;
    }

    /**
     * @param string $orderIdentifier
     * @return bool If the order identified by 'orderIdentifier' is already in the database.
     */
    private function orderExists(string $orderIdentifier) {
        return $this->em->find(Order::class, $orderIdentifier) !== null;
    }

    /**
     * Persists a new customer in the database, assuming there is no duplicate.
     * @param array $customerData Expected: [id, title, lastname, firstname, postal_code, city, email]
     */
    private function persistNewCustomer(array $customerData)
    {
        // Overriding optional values
        $customerInfo = [-1, -1, "", "", "", "", "", ""];
        for ($i=0; $i<count($customerData); $i++) {
            $customerInfo[$i] = $customerData[$i];
        }

        // Persisting the entity
        $customer = new Customer();
        $customer->setId((int)$customerInfo[0])
            ->setTitle($customerInfo[1])
            ->setLastname($customerInfo[2])
            ->setFirstname($customerInfo[3])
            ->setPostalCode($customerInfo[4])
            ->setCity($customerInfo[5])
            ->setEmail($customerInfo[6]);
        $this->em->persist($customer);
    }

    /**
     * Persist a new order in the database, assuming the related customer does exist and there is no duplicate.
     * @param array $orderData Expected: [purchase_identifier, customer_id, product_id, quantity, price, currency, date]
     */
    private function persistNewOrder(array $orderData)
    {
        $customer = $this->em->find(Customer::class, (int)$orderData[1]);
        $date = \DateTime::createFromFormat('Y-m-d', $orderData[6]);

        $order = new Order();
        $order->setPurchaseIdentifier($orderData[0])
            ->setCustomer($customer)
            ->setProductId((int)$orderData[2])
            ->setQuantity((int)$orderData[3])
            ->setPrice((float)$orderData[4])
            ->setCurrency($orderData[5])
            ->setDate($date);

        $this->em->persist($order);
    }
}