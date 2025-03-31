<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/dashboard')]
class DashboardController extends AbstractController
{
    private OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    #[Route('/', name: 'app_admin_dashboard', methods: ['GET'])]
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        $orders = $paginator->paginate(
            $this->orderRepository->findBy([], ['id' => 'DESC']),
            $request->query->getInt('page', 1),
            5
        );

        return $this->render('admin/dashboard/index.html.twig', [
            'controller_name' => 'Admin/DashboardController',
            'orders' => $orders
        ]);
    }

    #[Route('/order/{id}', name: 'app_admin_order', methods: ['GET'])]
    public function  showOrder(Order $order): Response
    {
        return $this->render('admin/dashboard/order/details_order.html.twig', [
            'order' => $order
        ]);
    }
}
