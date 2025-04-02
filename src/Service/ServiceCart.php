<?php
namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ServiceCart
{
    
    private $productRepository;
    public function __construct(
        ProductRepository $productRepository
    )
    {
        $this->productRepository = $productRepository;
    }

    public function getCartInfo(SessionInterface $session){
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

        return [
            'carts' => $cartWithProducts,
            'total' => $total
        ];
    }
}


