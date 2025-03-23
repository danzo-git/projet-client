<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\SubCategory;
use App\Repository\ProductRepository;
use App\Repository\SubCategoryRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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

    public function index(SubCategory $subCategory, PaginatorInterface $paginator,Request $request): Response
    {
       $categorieName = $subCategory->getCategory()->getName();

        $products=  $this->productRepository->findBySubCategory($subCategory);
        $allProducts = $paginator->paginate(
            $products,
            $request->query->getInt('page', 1),
            10
        );
    
       
        return $this->render('filter/index.html.twig', [
            'controller_name' => 'FilterController',
            'allProducts' => $allProducts,
            'categorieName' => $categorieName,
        ]);
    }
}
