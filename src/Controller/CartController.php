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
use Symfony\Component\HttpFoundation\RequestStack;

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

    #[Route('/cart/add/{id}', name: 'app_cart_add', methods: ['POST'])]
    public function addToCart($id, Request $request, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $taille = $request->request->get('taille');
        
        // Si le produit existe déjà avec la même taille
        $productKey = $taille ? $id . '-' . $taille : $id;
        
        if (!empty($cart[$productKey])) {
            if (is_array($cart[$productKey])) {
                $cart[$productKey]['quantity']++;
            } else {
                // Convertir l'ancienne structure en nouvelle
                $cart[$productKey] = [
                    'quantity' => $cart[$productKey] + 1,
                    'taille' => $taille
                ];
            }
        } else {
            $cart[$productKey] = [
                'quantity' => 1,
                'taille' => $taille
            ];
        }
        
        $session->set('cart', $cart);
        
        $this->addFlash('success', 'Produit ajouté au panier');
        $redirectUrl = $request->headers->get('referer') 
        ?? $session->get('cart_redirect_url', $this->generateUrl('app_home'));

    return $this->redirect($redirectUrl);
    }

    #[Route('/cart/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function updateQuantity($id, Request $request, SessionInterface $session, ProductRepository $productRepository): Response
    {
        $cart = $session->get('cart', []);
        $quantity = (int)$request->request->get('quantity');
        $taille = $request->request->get('taille');
        
        // Construire la clé du produit
        $productKey = $id;
        foreach ($cart as $key => $item) {
            if (strpos($key, $id) === 0 && isset($item['taille'])) {
                $productKey = $key;
                break;
            }
        }
        
        if (!isset($cart[$productKey])) {
            return $this->json(['success' => false, 'message' => 'Produit non trouvé'], 404);
        }
        
        $product = $productRepository->find($id);
        if (!$product) {
            return $this->json(['success' => false, 'message' => 'Produit non trouvé'], 404);
        }
        
        if ($quantity > 0) {
            if (is_array($cart[$productKey])) {
                $cart[$productKey]['quantity'] = $quantity;
            } else {
                $cart[$productKey] = [
                    'quantity' => $quantity,
                    'taille' => $taille
                ];
            }
        } else {
            unset($cart[$productKey]);
        }
        
        $session->set('cart', $cart);
        
        // Calcul du nouveau total
        $total = 0;
        foreach ($cart as $key => $item) {
            $productId = explode('-', $key)[0];
            $product = $productRepository->find($productId);
            if ($product) {
                $itemQuantity = is_array($item) ? $item['quantity'] : $item;
                $total += $product->getPrice() * $itemQuantity;
            }
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
        
        // Trouver la clé du produit avec la bonne taille
        $productKey = $id;
        foreach ($cart as $key => $item) {
            if (strpos($key, $id) === 0) {
                $productKey = $key;
                break;
            }
        }
        
        if (!isset($cart[$productKey])) {
            return $this->json(['success' => false, 'message' => 'Produit non trouvé'], 404);
        }
        
        unset($cart[$productKey]);
        $session->set('cart', $cart);
        
        // Calcul du nouveau total
        $total = 0;
        foreach ($cart as $key => $item) {
            $productId = explode('-', $key)[0];
            $product = $productRepository->find($productId);
            if ($product) {
                $itemQuantity = is_array($item) ? $item['quantity'] : $item;
                $total += $product->getPrice() * $itemQuantity;
            }
        }
        
        return $this->json([
            'success' => true,
            'cartTotal' => $total,
            'cartCount' => count($cart)
        ]);
    }
}