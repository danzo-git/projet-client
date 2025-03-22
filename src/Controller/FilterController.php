<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\SubCategory;
use App\Repository\ProductRepository;
use App\Repository\SubCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FilterController extends AbstractController
{
    private $productRepository;
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }
    #[Route('{id}/filter', name: 'app_filter')]

    public function index(SubCategory $subCategory): Response
    {
       $categorieName = $subCategory->getCategory()->getName();

        
    
       
        return $this->render('filter/index.html.twig', [
            'controller_name' => 'FilterController',
            'allProducts' => $this->productRepository->findBySubCategory($subCategory),
            'categorieName' => $categorieName,
        ]);
    }
}
