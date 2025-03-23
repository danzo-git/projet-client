<?php

namespace App\Controller;


use App\Entity\SubCategory;
use App\Repository\ProductRepository;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
public function index(SubCategory $subCategory, PaginatorInterface $paginator, Request $request): Response
{
    $categorieName = $subCategory->getCategory()->getName();
    
    // Get price filter parameters
    $minPrice = $request->query->get('price_min');
    $maxPrice = $request->query->get('price_max');
    
    // Set default values if empty
    $minPrice = (!empty($minPrice) || $minPrice === '0') ? (int)$minPrice : 1;
    $maxPrice = !empty($maxPrice) ? (int)$maxPrice : 1000000;
    
    // Retrieve products with price filter
    $products = $this->productRepository->findByPriceBetween($minPrice, $maxPrice, $subCategory->getId());
    
    // Paginate results
    $allProducts = $paginator->paginate(
        $products,
        $request->query->getInt('page', 1),
        10
    );
    
    return $this->render('filter/index.html.twig', [
        'controller_name' => 'FilterController',
        'allProducts' => $allProducts,
        'categorieName' => $categorieName,
        'minPrice' => $minPrice,
        'maxPrice' => $maxPrice
    ]);
}
}
