<?php
// src/EventListener/SubCategoryRemovalListener.php
namespace App\EventListener;

use App\Entity\SubCategory;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class SubCategoryRemovalListener
{
    public function preRemove(SubCategory $subCategory, LifecycleEventArgs $args): void
    {
        $entityManager = $args->getObjectManager();
        
        // Parcourir tous les produits de la sous-catégorie
        foreach ($subCategory->getProducts() as $product) {
            // Si le produit n'appartient qu'à cette sous-catégorie, le supprimer
            if ($product->getSubCategories()->count() <= 1) {
                $entityManager->remove($product);
            }
        }
    }
}