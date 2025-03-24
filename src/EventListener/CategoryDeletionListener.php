<?php
// src/EventListener/CategoryDeletionListener.php
namespace App\EventListener;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: Category::class)]
class CategoryDeletionListener
{
    public function preRemove(Category $category, PreRemoveEventArgs $eventArgs)
    {
        $entityManager = $eventArgs->getObjectManager();
        
        // Parcourir toutes les sous-catégories de la catégorie
        foreach ($category->getSubCategories() as $subCategory) {
            // Parcourir tous les produits de chaque sous-catégorie
            foreach ($subCategory->getProducts() as $product) {
                // Vérifier si le produit n'appartient qu'à cette sous-catégorie
                if ($product->getSubCategories()->count() == 1) {
                    // Supprimer le produit
                    $entityManager->remove($product);
                }
            }
        }
        
        // Flush pour s'assurer que les produits sont supprimés avant les sous-catégories
        $entityManager->flush();
    }
}