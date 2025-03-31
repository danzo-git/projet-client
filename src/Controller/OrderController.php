<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Form\OrderType;
use App\Service\ServiceCart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    private $entityManager;
    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }
    #[Route('/order', name: 'app_order')]
    public function index(Request $request,
     ServiceCart $serviceCart,SessionInterface $session): Response
    {
       
         $cartInfo= $serviceCart->getCartInfo($session);
        $order= new Order();
        $form=$this->createForm(OrderType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if($order->isPaymentOnDelivery()){
                if(!empty($cartInfo['total'])){
                    $order->setTotalPrice($cartInfo['total']);
                    $this->entityManager->persist($order);
                    
                    $this->entityManager->flush();
                    foreach($cartInfo['carts'] as $cart){
                        $orderProduct = new OrderProduct();
                        $orderProduct->setOrder($order);
                        $orderProduct->setProduct($cart['product']);
                        $orderProduct->setQuantity($cart['quantity']);
                        // $orderProduct->setPrice($cart['product']->getPrice());
                        $this->entityManager->persist($orderProduct);
                        $this->entityManager->flush();
                }
                
                }
                $cart = $session->set('cart', []);
            
                $this->addFlash('success', 'Order created successfully');
                return $this->redirectToRoute('app_home');
            }
        }
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
