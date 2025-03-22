<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\SubCategory;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SubCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class NavigationController extends AbstractController
{
    #[Route('/navigation', name: 'app_navigation')]
    public function index(
     
    CategoryRepository $categoryRepository ,
    SubCategoryRepository $subCategoryRepository  ): Response
    {

        return $this->render('partials/navigation.html.twig', [
            'controller_name' => 'NavigationController',
            'categories' => $categoryRepository->findAll(),
            'subCategories' => $subCategoryRepository->findAll(),
        ]);
    }
}
