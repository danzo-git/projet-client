<?php

namespace App\Twig;

use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SubCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CategoryExtension extends AbstractExtension
{
    private $categoryRepository;
    private $subCategoryRepository;

    private $productRepository;
    private $entityManager;
    public function __construct(
        CategoryRepository $categoryRepository,
        SubCategoryRepository $subCategoryRepository,
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->subCategoryRepository = $subCategoryRepository;
        $this->productRepository = $productRepository;
        $this->entityManager = $entityManager;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('get_all_categories', [$this, 'getAllCategories']),
            new TwigFunction('get_all_subcategories', [$this, 'getAllSubCategories']),
            new TwigFunction('get_product_by_id', [$this, 'getProductById']),
        ];
    }

    public function getAllCategories()
    {
        return $this->categoryRepository->findAll();
    }

    public function getAllSubCategories()
    {
        return $this->subCategoryRepository->findAll();
    }

    public function getProductById($id)
    {
        // Convertir l'ID en entier
        $id = intval($id);
        
        $product = $this->entityManager->getRepository(Product::class)->find($id);
        if (!$product) {
            return null; // Handle missing product
        }
        return $product;
    }
    
}   
