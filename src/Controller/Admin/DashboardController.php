<?php

namespace App\Controller\Admin;
use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\PrintService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/dashboard')]
class DashboardController extends AbstractController
{
    private PrintService $printService;
    private OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository, PrintService $printService)
    {
        $this->orderRepository = $orderRepository;
        $this->printService = $printService;
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

        //return $response;
    }

    #[Route('/order/{id}/complete', name: 'app_admin_order_isComplete', methods: ['GET'])]
    public function  editOrder(Order $order ,EntityManagerInterface  $entityManager): Response
    {
        $order=$this->orderRepository->find($order->getId());
        $order->setIsCompleted(true);
        $entityManager->flush();
        $this->addFlash('success', 'La commande a bien été complétée');

        return $this->redirectToRoute('app_admin_dashboard');
    }

    #[Route('/order/{id}/delete', name: 'app_admin_order_delete', methods: ['GET'])]
    public function  deleteOrder(Order $order ,EntityManagerInterface  $entityManager): Response{
        $entityManager->remove($order);
        $entityManager->flush();
        $this->addFlash('success', 'La commande a bien été supprimée');
        return $this->redirectToRoute('app_admin_dashboard');
    }

    #[Route('/admin/order/{id}/print', name: 'app_admin_order_print', methods: ['GET'])]
    public function  printOrder(Order $order): Response{
        return $this->printService->printOrder($order);
    }
}
