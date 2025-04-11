<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Order;
use App\Entity\OrderProduct;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Service\ServiceCart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

use App\Service\EmailService;
use App\Service\PrintService;
final class OrderController extends AbstractController
{
    private $emailService;
    private $printService;
   
    private $orderRepository;
    private $entityManager;
    public function __construct(
        EntityManagerInterface $entityManager,
        OrderRepository $orderRepository,
        EmailService $emailService  ,
        PrintService $printService      
    )
    {
        $this->entityManager = $entityManager;
        $this->orderRepository = $orderRepository;
        $this->emailService = $emailService;
        $this->printService = $printService;
       
    }
    #[Route('/order', name: 'app_order')]
public function index(Request $request, ServiceCart $serviceCart, SessionInterface $session): Response
{
    // Vérifier si l'utilisateur est connecté
    if (!$this->getUser()) {
        // Ajouter un message flash
        $this->addFlash('danger', 'Veuillez vous connecter pour passer une commande');
        // Sauvegarder l'URL actuelle dans la session pour rediriger après la connexion
        $session->set('redirect_url', $this->generateUrl('app_order'));
        // Rediriger vers la page de connexion
        return $this->redirectToRoute('app_login');
    }
    
    $cartInfo = $serviceCart->getCartInfo($session);
    
    // Vérifier si le panier est vide
    if (empty($cartInfo['carts'])) {
        $this->addFlash('warning', 'Votre panier est vide');
        return $this->redirectToRoute('app_home');
    }
    
    $order = new Order();
    
    // Pré-remplissage possible ici si nécessaire plus tard
    // Pour le moment, laissons l'utilisateur remplir le formulaire
    
    $form = $this->createForm(OrderType::class, $order);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        if ($order->isPaymentOnDelivery()) {
            if (!empty($cartInfo['total'])) {
                // 1. Calcul du total
                $order->setTotalPrice($cartInfo['total']);
                
                // 2. Persist et flush de la commande
                $this->entityManager->persist($order);
                $this->entityManager->flush();

                // 3. Ajout des produits de la commande
                foreach ($cartInfo['carts'] as $cart) {
                    $orderProduct = new OrderProduct();
                    $orderProduct->setOrder($order)
                                ->setProduct($cart['product'])
                                ->setQuantity($cart['quantity']);
                    $this->entityManager->persist($orderProduct);
                }
                
                // 4. Un seul flush pour tous les produits
                $this->entityManager->flush();

                // 5. Vidage du panier
                $session->set('cart', []);
                
                // 6. Envoi d'email
                $this->emailService->sendOrderConfirmation($order);

                // 7. Message flash
                $this->addFlash('success', 'Commande envoyée avec succès');

                // 8. Redirection vers une nouvelle route pour l'affichage
                return $this->redirectToRoute('order_confirmation', ['id' => $order->getId()]);
            }
        }
        else {
            if (!empty($cartInfo['total'])) {
                // 1. Calcul du total
                $order->setTotalPrice($cartInfo['total']);
                
                // 2. Persist et flush de la commande
                $this->entityManager->persist($order);
                $this->entityManager->flush();

                // 3. Ajout des produits de la commande
                foreach ($cartInfo['carts'] as $cart) {
                    $orderProduct = new OrderProduct();
                    $orderProduct->setOrder($order)
                                ->setProduct($cart['product'])
                                ->setQuantity($cart['quantity']);
                    $this->entityManager->persist($orderProduct);
                }
                
                // 4. Un seul flush pour tous les produits
                $this->entityManager->flush();

                // 5. Vidage du panier
                $session->set('cart', []);
                
                // 6. Envoi d'email
                $this->emailService->sendOrderConfirmation($order);

                // 7. Message flash
                $this->addFlash('success', 'Commande envoyée avec succès');

                // 8. Redirection vers une nouvelle route pour l'affichage
                return $this->redirectToRoute('order_confirmation', ['id' => $order->getId()]);
            }
        }
    }

    return $this->render('order/index.html.twig', [
        'form' => $form->createView(),
        'cartInfos' => $cartInfo['carts'],
        'total' => $cartInfo['total'] 
    ]);
}

#[Route('/order/confirmation/{id}', name: 'order_confirmation')]
public function confirmation(Order $order): Response
{
    // Affiche d'abord la page de confirmation avec le message flash
    // puis propose le téléchargement du PDF
    $this->addFlash('success', 'Commande envoyée avec succès');
    return $this->render('order/confirmation.html.twig', [
        'order' => $order,
        'downloadUrl' => $this->generateUrl('order_download', ['id' => $order->getId()])
    ]);
}

#[Route('/order/download/{id}', name: 'order_download')]
public function download(Order $order, PrintService $printService): Response
{
    // Génère et retourne directement le PDF
    return $printService->printOrder($order);
}


    #[Route('city/{id}/shipping/cost', name: 'app_city_shipping_cost')]
    public function shippingCost(City $city): Response
    {
        $cityShippingCost = $city->getPrice();

        return new Response(json_encode(['status' => 200, "message"=>"success",'content' => $cityShippingCost]));
    }


   
}
