<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
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
}