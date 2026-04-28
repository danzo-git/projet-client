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
    
        foreach ($cart as $key => $item) {
            // Extraire l'ID du produit de la clé (peut contenir la taille)
            $id = explode('-', $key)[0];
            $product = $this->productRepository->find($id);
            
            if (!$product) {
                continue;
            }
            
            $cartItem = [
                'product' => $product,
                'quantity' => is_array($item) ? $item['quantity'] : $item,
            ];
            
            // Ajouter la taille si elle existe
            if (isset($item['taille'])) {
                $cartItem['taille'] = $item['taille'];
            }
            
            $cartWithProducts[] = $cartItem;
            $total += $product->getPrice() * $cartItem['quantity'];
        }
    
        return [
            'carts' => $cartWithProducts,
            'total' => $total
        ];
    }
}


