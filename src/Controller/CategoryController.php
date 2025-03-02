<?php

namespace App\Controller;
Use App\Entity\Category;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
 class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories=$categoryRepository->findAll();
        return $this->render('category/index.html.twig', [
            // 'controller_name' => 'CategoryController',
            'categories'=>$categories
        ]);
    }

    #[Route('/category/create', name: 'app_category_create')]
    public function addCategory(EntityManagerInterface $entityManager,Request $request): Response
    {
        $category=new Category();
        $form=$this->createForm(CategoryFormType::class,$category);
        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid()){
            $entityManager->persist($category);
            $entityManager->flush();
        }
        return $this->render('category/create.html.twig', [
            'controller_name' => 'CategoryController',
            'form'=>$form->createView(),
        ]);
    }


    #[Route('/category/{id}/update', name: 'app_category_update')]
    public function updateCategory(Category $category,EntityManagerInterface $entityManager,Request $request): Response
    {
       
        $form=$this->createForm(CategoryFormType::class,$category);
        $form->handleRequest($request);
        if($form->isSubmitted()&& $form->isValid()){
            $entityManager->flush();
        }
        return $this->render('category/update.html.twig', [
            'controller_name' => 'CategoryController',
            'form'=>$form->createView(),
        ]);
    }

    #[Route('/category/{id}/delete', name: 'app_category_delete')]
    public function deleteCategory(Category $category,EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($category);
        $entityManager->flush();
        return $this->redirectToRoute('app_category');
    }

}
