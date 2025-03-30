<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Order;
use App\Form\OrderType;
use App\Service\ServiceCart;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    #[Route('/order', name: 'app_order')]
    public function index(Request $request, ServiceCart $serviceCart,SessionInterface $session): Response
    {
         $cartInfo= $serviceCart->getCartInfo($session);
        $order= new Order();
        $form=$this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        return $this->render('order/index.html.twig', [
            'form' => $form->createView(),
            'cartInfos' => $cartInfo['carts'],
            'total' => $cartInfo['total'] 
        ]);
    }


    #[Route('city/{id}/shipping/cost', name: 'app_city_shipping_cost')]
    public function shippingCost(City $city): Response
    {
        $cityShippingCost = $city->getPrice();

        return new Response(json_encode(['status' => 200, "message"=>"success",'content' => $cityShippingCost]));
    }
}
