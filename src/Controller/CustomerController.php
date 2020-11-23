<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CustomerController
 *
 * @Route("/customers", name="customer_")
 * @package App\Controller
 */
class CustomerController extends AbstractController
{
    /**
     * @Route("/", name="list", methods={"GET"})
     * @return Response
     */
    public function index()
    {
        return $this->render('customer/list.html.twig');
    }

    /**
     * @Route("/{customerId}/orders", name="order_list", methods={"GET"})
     * @param int $customerId
     * @return Response
     */
    public function listOrders(int $customerId)
    {
        return $this->render('customer/orders.html.twig', [
            'customerId' => $customerId
        ]);
    }
}