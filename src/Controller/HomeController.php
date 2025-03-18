<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SubCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        SubCategoryRepository $subCategoryRepository
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
            'productsByRandom' => $productsByRandom,
            'products' => $products,
            'newProducts' => $newProducts,
            'topProducts' => $topProducts,
            'subCategories' => $subCategories,
        ]);
    }
}
