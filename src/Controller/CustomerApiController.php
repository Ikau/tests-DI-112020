<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller implementing API endpoint for entity 'Customer'.
 *
 * Current implemented routes:
 * - GET /api/customers/: list of all existing customers
 * - GET /api/customers/{customer_id}/orders/: list of all orders for the related customer
 *
 * @Route("/api/customers", name="api_customers_")
 * @package App\Controller
 */
class CustomerApiController extends AbstractController
{
    /**
     * @Route("/", name="list", methods={"GET"})
     * @param CustomerRepository $customerRepository
     * @return JsonResponse
     */
    public function list(CustomerRepository $customerRepository): JsonResponse
    {
        $results = $customerRepository->findAll();
        $customers = [];

        foreach ($results as $customer) {
            $customers[] = $customer->toAssociativeArray();
        }
        return $this->json($customers);
    }

    /**
     * @Route("/{id}/orders", name="list_orders", methods={"GET"}, requirements={"customer_id"="\d+"})
     * @param Customer $customer
     * @return JsonResponse
     */
    public function showOrders(Customer $customer) : JsonResponse
    {
        $results = $customer->getOrders();

        $orders = [];
        foreach ($results as $order) {
            $orders[] = $order->toAssociativeArray();
        }

        return $this->json($orders);
    }

    /**
     * @Route("/{id}", name="show", methods={"GET"}, requirements={"id"="\d+"})
     * @param Customer $customer
     * @return JsonResponse
     */
    public function find(Customer $customer)
    {
        if ($customer === null) {
            return $this->json([]);
        }

        return $this->json($customer->toAssociativeArray());
    }
}