<?php

namespace App\Controller;

use App\Entity\AddProductHistory;
use App\Entity\Product;
use App\Form\ProductUpdateType;
use App\Form\ProductType;
use App\Entity\ProductImage;
use App\Form\AddProductHistoryType;
use App\Repository\AddProductHistoryRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/product')]
final class ProductController extends AbstractController
{
    #[Route(name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

 #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
 
 public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
 {
     $product = new Product();
     // Nous ne pré-ajoutons pas d'image par défaut
     
     $form = $this->createForm(ProductType::class, $product);
     $form->handleRequest($request);
 
     if ($form->isSubmitted() && $form->isValid()) {
         // Récupérer toutes les images soumises
         $productImages = $product->getProductImages();
         
         // Pour chaque image dans la collection
         foreach ($productImages as $key => $productImage) {
             // Récupérer le fichier
             $imageForm = $form->get('productImages')->get($key);
             $imageFile = $imageForm->get('imagePath')->getData();
             
             // Si un fichier a été téléchargé
             if ($imageFile) {
                 $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                 $safeFilename = $slugger->slug($originalFilename);
                 $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
 
                 try {
                     $imageFile->move(
                         $this->getParameter('images_directory'),
                         $newFilename
                     );
                     
                     // Définir le chemin de l'image
                     $productImage->setImagePath($newFilename);
                 } catch (FileException $e) {
                     $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image.');
                 }
             } else {
                 // Si pas d'image, on retire cette entrée de la collection
                 $product->removeProductImage($productImage);
             }
         }
 
         $entityManager->persist($product);
         $entityManager->flush();

         $stockHistory = new AddProductHistory();
         $stockHistory->setProduct($product);
         $stockHistory->setQuantity($product->getStock());
         $stockHistory->setCreatedAt(new \DateTimeImmutable());
         $entityManager->persist($stockHistory);
         $entityManager->flush();
         $this->addFlash('success', 'Votre produit a été ajouté');
         return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
     }
 
     return $this->render('product/new.html.twig', [
         'product' => $product,
         'form' => $form,
     ]);
 }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
{
    $form = $this->createForm(ProductUpdateType::class, $product);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Traiter les images à supprimer
        $deleteImages = [];
        if ($request->request->has('delete_images')) {
            $deleteImages = $request->request->all()['delete_images'];
        }
        
        foreach ($deleteImages as $imageId) {
            $image = $entityManager->getRepository(ProductImage::class)->find($imageId);
            if ($image && $image->getProduct() === $product) {
                // Supprimer le fichier physique
                $imagePath = $this->getParameter('images_directory') . '/' . $image->getImagePath();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
                // Supprimer de la base de données
                $product->removeProductImage($image);
                $entityManager->remove($image);
            }
        }
        
        // Traiter les images existantes et nouvelles
        $productImages = $product->getProductImages();
        
        // Pour chaque image dans la collection
        foreach ($productImages as $key => $productImage) {
            // Vérifier si le formulaire contient l'index de cette image
            if (!$form->get('productImages')->has($key)) {
                continue;
            }
            
            // Récupérer le fichier
            $imageForm = $form->get('productImages')->get($key);
            $imageFile = $imageForm->get('imagePath')->getData();
            
            // Si un fichier a été téléchargé
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    
                    // Définir le chemin de l'image
                    $productImage->setImagePath($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image.');
                }
            } else if (!$productImage->getImagePath()) {
                // Si pas d'image et pas de chemin existant, on retire cette entrée
                $product->removeProductImage($productImage);
                $entityManager->remove($productImage);
            }
            // Si pas de fichier mais un chemin existe déjà, on garde l'image existante
        }

        $entityManager->flush();
        $this->addFlash('success', 'Votre produit a été modifié avec succès');
        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

    return $this->render('product/edit.html.twig', [
        'product' => $product,
        'form' => $form,
    ]);
}

    #[Route('/{id}', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($product);
            $this->addFlash('danger', 'votre produit a ete supprimé');

            $entityManager->flush();

        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }


 #[Route('/{id}/stock', name: 'app_product_stock', methods: ['GET', 'POST'])]
 public function stock($id,Request $request, EntityManagerInterface $entityManager,ProductRepository $productRepository): Response
 {
    $stockHistory = new AddProductHistory();
     $form = $this->createForm(AddProductHistoryType::class, $stockHistory);
     $form->handleRequest($request);
     $product=$productRepository->find($id);
    if ($form->isSubmitted() && $form->isValid()) {
        if($stockHistory->getQuantity()>0){
          $newQte=$product->getStock() + $stockHistory->getQuantity();  
          $product->setStock($newQte);
          $stockHistory->setProduct($product);
          $stockHistory ->setCreatedAt(new \DateTimeImmutable());
          $entityManager->persist($stockHistory);
          $entityManager->flush(); 
          $this->addFlash('success', 'votre stock a ete modifié');
          return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }
        else{
            $this->addFlash('danger', 'votre stock doit etre superieur a 0');
            return $this->redirectToRoute('app_product_stock', ['id'=>$product->getId()], Response::HTTP_SEE_OTHER);

        }
    }
       
      
     return $this->render('product/stock.html.twig', [
         'product' => $product,
         'form' => $form->createView(),
     ]);

}

#[Route('/{id}/stock/history', name: 'app_product_stock_history', methods: ['GET'])]
    public function stockHistory( Product $product,ProductRepository $productRepository, AddProductHistoryRepository $addProductHistory): Response{
        
        $addProductHistory=$addProductHistory->findBy(['product'=>$product],['createdAt' => 'DESC']); 
        // dd($addProductHistory);
        return $this->render('product/stockHistory.html.twig', [
            // 'product' => $product,
            'addProductHistories' => $addProductHistory,
        ]);
    }
}