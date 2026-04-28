<?php

namespace App\Controller;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
final class SearchController extends AbstractController
{
    public function __construct(private readonly ProductRepository $productRepository) {}
    #[Route('/search', name: 'app_search', methods: ['POST'])]
    public function index(Request $request, CsrfTokenManagerInterface $csrfTokenManager): Response
    {
        
       // Vérification que c'est bien une requête POST
    if (!$request->isMethod('POST')) {
        return $this->redirectToRoute('app_home');
    }

    // Récupération et validation de la requête
    $searchQuery = trim($request->request->get('query', ''));
    
    // Gestion du cas où la recherche est vide
    if (empty($searchQuery)) {
        $this->addFlash('warning', 'Veuillez entrer un terme de recherche');
        return $this->redirectToRoute('app_home'); // Ou retourner à la page précédente
    }

    // Recherche des produits
    try {
        $products = $this->productRepository->searchResult($searchQuery);
        
        return $this->render('search/index.html.twig', [
            'allProducts' => $products,
            'search_query' => $searchQuery // Important pour afficher dans le template
        ]);
        
    } catch (\Exception $e) {
        $this->addFlash('error', 'Une erreur est survenue lors de la recherche');
        return $this->redirectToRoute('app_home');
    }
}
}