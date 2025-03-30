<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\CartService;
use App\Service\ServiceCart;

class CartController extends AbstractController
{
    private $productRepository;
    private $cartService;

    public function __construct(ProductRepository $productRepository, ServiceCart $cartService)
    {
        $this->productRepository = $productRepository;
        $this->cartService = $cartService;
    }

    #[Route('/cart', name: 'app_cart', methods: ['GET'])]
    public function index(SessionInterface $session): Response
    {
        
        $cartInfo= $this->cartService->getCartInfo($session);
        return $this->render('cart/index.html.twig', [
            'carts' => $cartInfo['carts'], 
            'total' => $cartInfo['total'] 
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add', methods: ['GET'])]
    public function addToCart($id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        if (!empty($cart[$id])) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }
        $session->set('cart', $cart);
        
        $this->addFlash('success', 'Produit ajouté au panier');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/cart/update/{id}', name: 'app_cart_update', methods: ['POST'])]
public function updateQuantity($id, Request $request, SessionInterface $session, ProductRepository $productRepository): Response
{
    $cart = $session->get('cart', []);
    $quantity = (int)$request->request->get('quantity');
    
    if (!isset($cart[$id])) {
        return $this->json(['success' => false, 'message' => 'Produit non trouvé'], 404);
    }
    
    $product = $productRepository->find($id);
    if (!$product) {
        return $this->json(['success' => false, 'message' => 'Produit non trouvé'], 404);
    }
    
    if ($quantity > 0) {
        $cart[$id] = $quantity;
    } else {
        unset($cart[$id]);
    }
    
    $session->set('cart', $cart);
    
    // Calcul du nouveau total
    $total = 0;
    foreach ($cart as $id => $qty) {
        $product = $productRepository->find($id);
        $total += $product->getPrice() * $qty;
    }
    
    return $this->json([
        'success' => true,
        'cartTotal' => $total,
        'cartCount' => count($cart)
    ]);
}

#[Route('/cart/remove/{id}', name: 'app_cart_remove', methods: ['POST'])]
public function removeFromCart($id, Request $request, SessionInterface $session, ProductRepository $productRepository): Response
{
    $cart = $session->get('cart', []);
    
    if (!isset($cart[$id])) {
        return $this->json(['success' => false, 'message' => 'Produit non trouvé'], 404);
    }
    
    unset($cart[$id]);
    $session->set('cart', $cart);
    
    // Calcul du nouveau total
    $total = 0;
    foreach ($cart as $id => $qty) {
        $product = $productRepository->find($id);
        $total += $product->getPrice() * $qty;
    }
    
    return $this->json([
        'success' => true,
        'cartTotal' => $total,
        'cartCount' => count($cart)
    ]);
}
}