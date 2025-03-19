<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\SubCategory;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SubCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        SubCategoryRepository $subCategoryRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Get categories with random products
        $productsByRandom = $categoryRepository->findBy([], ['name' => 'ASC']);
        
        // Get all products
        $products = $productRepository->findAll();
        
        // Get new products (you can modify this criteria as needed)
        $newProducts = $productRepository->findBy([], ['id' => 'DESC'], 10);
        
        // Get top products/sales (you can modify this criteria as needed)
        // This is just a placeholder, replace with your actual logic
        $topProducts = $productRepository->findBy([], ['id' => 'ASC'], 10);
        
        // Get all subcategories for the filter tabs
        $subCategories = $subCategoryRepository->findAll();
        
        return $this->render('home/index.html.twig', [
            'collectionsByRandom' => $productRepository->find3ProductsByRandomWithSubCategoryAndCategoryAndImagesAssociated($entityManager),
            'products' => $products,
            'newProducts' => $newProducts,
            'topProducts' => $topProducts,
            'subCategories' => $subCategories,
        ]);
    }


    #[Route('/{id}/show', name: 'app_product_show_customer', methods: ['GET'])]
    public function show(Product $product): Response
{
    $productCousins = $product->getSubCategories();
    $relatedProducts = [];

    foreach ($productCousins as $productCousin) {
        // Récupérer les produits de la sous-catégorie
        $products = $productCousin->getProducts()->getValues();

        // Fusionner les produits dans le tableau $relatedProducts
        $relatedProducts = array_merge($relatedProducts, $products);
    }

    // Filtrer pour retirer le produit actuel
    $relatedProducts = array_filter($relatedProducts, function ($relatedProduct) use ($product) {
        return $relatedProduct->getId() !== $product->getId();
    });

    // Supprimer les doublons (si nécessaire)
    $relatedProducts = array_unique($relatedProducts, SORT_REGULAR);

    return $this->render('home/show.html.twig', [
        'product' => $product,
        'relatedProducts' => $relatedProducts, // Passer les produits connexes au template
    ]);
}



#[Route('/{id}/showCollectionProduct', name: 'app_sub_category_show_collection_product', methods: ['GET'])]
public function showCollectionProduct(Category $category): Response{
    $categorieName = $category->getName();
  
    
    $categoryProducts= $category->getSubCategories()->getValues();
     // Initialize an array to store all products
     $allProducts = [];

     // Loop through each subcategory and get its products
     foreach ($category->getSubCategories() as $subCategory) {
         // Add the products of the current subcategory to the $allProducts array
         $allProducts = array_merge($allProducts, $subCategory->getProducts()->toArray());
     }
    
    return $this->render('home/showCollectionProduct.html.twig', [
        'categoryProducts' => $categoryProducts,
        'categorieName' => $categorieName,
        'allProducts' => $allProducts
    ]);
}
}
