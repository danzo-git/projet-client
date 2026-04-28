<?php
// src/EventListener/CategoryRemovalListener.php
namespace App\EventListener;

use App\Entity\Category;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class CategoryRemovalListener
{
    public function preRemove(Category $category,  LifecycleEventArgs  $args): void
    {
        $entityManager = $args->getObjectManager();
        $productRepository = $entityManager->getRepository('App\Entity\Product');
        
        // Récupérer toutes les sous-catégories
        $subCategories = $category->getSubCategories();
        
        foreach ($subCategories as $subCategory) {
            // Pour chaque sous-catégorie, récupérer les produits associés
            $products = $subCategory->getProducts();
            
            foreach ($products as $product) {
                // Vérifier si ce produit n'est pas associé à d'autres sous-catégories d'autres catégories
                $otherSubCategories = false;
                foreach ($product->getSubCategories() as $sc) {
                    if ($sc->getCategory()->getId() !== $category->getId()) {
                        $otherSubCategories = true;
                        break;
                    }
                }
                
                // Si le produit n'appartient qu'à des sous-catégories de cette catégorie, le supprimer
                if (!$otherSubCategories) {
                    $entityManager->remove($product);
                }
            }
        }
    }
}