<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\SubCategory;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SubCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
            'categories' => $categoryRepository->findAll(),
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
public function showCollectionProduct(
    Category $category, 
    Request $request, 
    ProductRepository $productRepository,
    
    PaginatorInterface $paginator
): Response {
    // Récupérer les paramètres de filtre
    $subCategoryIds = $request->query->all('subcategories');
   // Dans votre méthode de contrôleur
    $minPrice = $request->query->get('price_min');
    $maxPrice = $request->query->get('price_max');

// Convertir en valeurs par défaut si null ou vide
$minPrice = (!empty($minPrice) || $minPrice === '0') ? (int)$minPrice : 1;
$maxPrice = !empty($maxPrice) ? (int)$maxPrice : 1000000;

    // $brands = $request->query->all('brands');
    $limit = max(1, (int)$request->query->get('limit', 10));
    $page = max(1, (int)$request->query->get('page', 1));
    
    // Utilisez le repository pour récupérer directement les produits filtrés
    // Cela évitera les problèmes de conversion entre Collection et Array
    $qb = $productRepository->createQueryBuilder('p')
        ->join('p.subCategories', 'sc')
        ->join('sc.category', 'c')
        ->where('c.id = :categoryId')
        ->setParameter('categoryId', $category->getId());
    
    // Filtrer par sous-catégories si spécifié
    if (!empty($subCategoryIds)) {
        $qb->andWhere('sc.id IN (:subCategoryIds)')
           ->setParameter('subCategoryIds', $subCategoryIds);
    }
    
    // Filtrer par prix si défini
    if (!empty($minPrice)) {
        $qb->andWhere('p.price >= :minPrice')
           ->setParameter('minPrice', $minPrice);
    }
    
    if (!empty($maxPrice)) {
        $qb->andWhere('p.price <= :maxPrice')
           ->setParameter('maxPrice', $maxPrice);
    }
    
  
    
    
    // Exécuter la requête pour obtenir le nombre total de produits
    $countQb = clone $qb;
    $totalProducts = count($countQb->getQuery()->getResult());
    
    
   $Products = $qb->getQuery()->getResult();
    
    
  
    $paginatedProducts = $paginator->paginate(
    $Products,
    $page,
    $limit
);
    
    
    return $this->render('home/showCollectionProduct.html.twig', [
        'categoryProducts' => $category->getSubCategories(),
        'categorieName' => $category->getName(),
        'allProducts' => $paginatedProducts,
        'selectedSubCategories' => $subCategoryIds,
        'minPrice' => $minPrice,
        'maxPrice' => $maxPrice,
        // 'brands' => $brands,
    
        'limit' => $limit,
        'currentPage' => $page,
       
        'totalProducts' => $totalProducts
    ]);
}

}