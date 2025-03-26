<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    private $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    #[Route('/cart', name: 'app_cart', methods: ['GET'])]
    public function index(SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $cartWithProducts = [];
        $total = 0;

        foreach ($cart as $id => $quantity) {
            $product = $this->productRepository->find($id);
            $cartWithProducts[] = [
                'product' => $product,
                'quantity' => $quantity
            ];
            $total += $product->getPrice() * $quantity;
        }
        
        return $this->render('cart/index.html.twig', [
            'carts' => $cartWithProducts,
            'total' => $total
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

    #[Route('/cart/remove/{id}', name: 'app_cart_remove', methods: ['GET'])]
    public function removeFromCart($id, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        unset($cart[$id]);
        $session->set('cart', $cart);
        
        $this->addFlash('success', 'Produit retiré du panier');
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/update/{id}', name: 'app_cart_update', methods: ['POST'])]
    public function updateQuantity($id, Request $request, SessionInterface $session): Response
    {
        $cart = $session->get('cart', []);
        $quantity = $request->request->get('quantity');
        
        if ($quantity > 0) {
            $cart[$id] = $quantity;
        } else {
            unset($cart[$id]);
        }
        
        $session->set('cart', $cart);
        
        return $this->redirectToRoute('app_cart');
    }
}