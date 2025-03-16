<?php

namespace App\Controller;

use App\Repository\ProductRepository;

use App\Repository\SubCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

 class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'products' => $productRepository->find10Biens($productRepository->getEntityManager()),
            'newProducts' => $productRepository->findBy([], ['id' => 'DESC'], 10),
            'productsByRandom' => $productRepository->find3ProductsByRandomWithSubCategoryAndCategoryAndImagesAssociated($productRepository->getEntityManager()),
        ]);
    }
}
