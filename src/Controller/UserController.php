<?php

namespace App\Controller;
use App\Entity\User;
use App\Form\UserFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dom\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('admin/user', name: 'app_user')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        return $this->render('user/index.html.twig', [
            'users' => $users
        ]);
    }

   

    #[Route('admin/user/{id}/edit', name: 'app_user_edit')]
public function edit(EntityManagerInterface $entityManager, Request $request, User $user): Response
{
    $form = $this->createForm(UserFormType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        $this->addFlash('success', 'Utilisateur modifié avec succès');
        return $this->redirectToRoute('app_user');
    }

    return $this->render('user/edit.html.twig', [
        'form' => $form->createView(),
    ]);
}

    #[Route('admin/user/{id}/delete', name: 'app_user_delete')]
    public function delete(User $user,EntityManagerInterface $entityManager): Response{
        $entityManager->remove($user);
        $entityManager->flush();
        $this->addFlash('success', 'Utilisateur supprimée avec succès');
        return $this->redirectToRoute('app_user');
    }
}
