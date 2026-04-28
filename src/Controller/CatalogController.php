<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class CatalogController extends AbstractController
{
    private $productRepository;
    private $urlGenerator;

    public function __construct(
        ProductRepository $productRepository,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->productRepository = $productRepository;
        $this->urlGenerator = $urlGenerator;
    }

    #[Route('/facebook-catalog.xml', name: 'app_facebook_catalog')]
    public function facebookCatalog(): Response
    {
        try {
            $products = $this->productRepository->findAll();
            
            // Créer le document XML
            $dom = new \DOMDocument('1.0', 'UTF-8');
            $dom->formatOutput = true;
            
            // Créer l'élément racine rss
            $rss = $dom->createElement('rss');
            $rss->setAttribute('version', '2.0');
            $rss->setAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
            $dom->appendChild($rss);
            
            // Créer le canal
            $channel = $dom->createElement('channel');
            $rss->appendChild($channel);
            
            // Ajouter les éléments du canal
            $title = $dom->createElement('title', 'RTC Boutique - Catalogue Produits');
            $channel->appendChild($title);
            
            $link = $dom->createElement('link', $this->generateUrl('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL));
            $channel->appendChild($link);
            
            $description = $dom->createElement('description', 'Catalogue des produits RTC pour Facebook');
            $channel->appendChild($description);
            
            // Ajouter chaque produit
            foreach ($products as $product) {
                if ($product->getStock() > 0 && count($product->getProductImages()) > 0) {
                    $item = $dom->createElement('item');
                    $channel->appendChild($item);
                    
                    // ID unique du produit
                    $idNode = $dom->createElement('g:id', $product->getId());
                    $item->appendChild($idNode);
                    
                    // Titre du produit
                    $titleNode = $dom->createElement('title', htmlspecialchars($product->getName(), ENT_XML1));
                    $item->appendChild($titleNode);
                    
                    // Description
                    $descText = $product->getDescription() ?: $product->getName();
                    $descNode = $dom->createElement('description', htmlspecialchars($descText, ENT_XML1));
                    $item->appendChild($descNode);
                    
                    // Lien vers la page produit
                    $linkUrl = $this->generateUrl('app_product_show_customer', [
                        'id' => $product->getId()
                    ], UrlGeneratorInterface::ABSOLUTE_URL);
                    $linkNode = $dom->createElement('link', $linkUrl);
                    $item->appendChild($linkNode);
                    
                    // Image principale
                    $mainImage = $product->getProductImages()->first();
                    if ($mainImage) {
                        $imageUrl = $this->generateUrl('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL) . 
                                    'uploads/' . $mainImage->getImagePath();
                        $imageNode = $dom->createElement('g:image_link', $imageUrl);
                        $item->appendChild($imageNode);
                        
                        // Images additionnelles (max 10)
                        $additionalImages = $product->getProductImages()->slice(1, 9); // Skip first, take 9 more
                        foreach ($additionalImages as $image) {
                            $additionalImageUrl = $this->generateUrl('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL) . 
                                                'uploads/' . $image->getImagePath();
                            $addImageNode = $dom->createElement('g:additional_image_link', $additionalImageUrl);
                            $item->appendChild($addImageNode);
                        }
                    }
                    
                    // Disponibilité
                    $availNode = $dom->createElement('g:availability', $product->getStock() > 0 ? 'in stock' : 'out of stock');
                    $item->appendChild($availNode);
                    
                    // Prix
                    $priceText = $product->getPrice() . ' XOF';
                    $priceNode = $dom->createElement('g:price', $priceText);
                    $item->appendChild($priceNode);
                    
                    // Devise
                    $currencyNode = $dom->createElement('g:currency', 'XOF');
                    $item->appendChild($currencyNode);
                    
                    // Marque
                    if ($product->getBrand()) {
                        $brandNode = $dom->createElement('g:brand', htmlspecialchars($product->getBrand(), ENT_XML1));
                        $item->appendChild($brandNode);
                    }
                    
                    // Condition
                    $condNode = $dom->createElement('g:condition', 'new');
                    $item->appendChild($condNode);
                    
                    // Catégorie Google
                    $categories = $product->getSubCategories();
                    if ($categories->count() > 0) {
                        $category = $categories->first()->getCategory()->getName();
                        // Format de taxonomie Facebook standard
                        $fbCategory = 'Apparel & Accessories > Clothing';
                        if (stripos($category, 'ACCESSOIRES') !== false) {
                            $fbCategory = 'Apparel & Accessories > Jewelry';
                            if (stripos($categories->first()->getName(), 'MONTRE') !== false) {
                                $fbCategory = 'Apparel & Accessories > Jewelry > Watches';
                            }
                        } elseif (stripos($category, 'SACS') !== false) {
                            $fbCategory = 'Apparel & Accessories > Bags';
                        }
                        $catNode = $dom->createElement('g:google_product_category', htmlspecialchars($fbCategory, ENT_XML1));
                        $item->appendChild($catNode);
                        
                        // Catégorie produit
                        $prodTypeNode = $dom->createElement('g:product_type', htmlspecialchars($categories->first()->getName(), ENT_XML1));
                        $item->appendChild($prodTypeNode);
                    }
                    
                    // Tailles si disponibles
                    if ($product->getTaille()) {
                        $tailles = $product->getTaille();
                        if (is_array($tailles) && count($tailles) > 0) {
                            $sizeNode = $dom->createElement('g:size', implode(', ', $tailles));
                            $item->appendChild($sizeNode);
                        }
                    }
                    
                    // MPN (ID unique du produit dans votre système)
                    $mpnNode = $dom->createElement('g:mpn', 'RTC-' . $product->getId());
                    $item->appendChild($mpnNode);
                    
                    // Item Group ID (pour les produits variantes)
                    $itemGroupNode = $dom->createElement('g:item_group_id', 'RTC-GROUP-' . $product->getId());
                    $item->appendChild($itemGroupNode);
                }
            }
            
            $xmlContent = $dom->saveXML();
            
            $response = new Response($xmlContent);
            $response->headers->set('Content-Type', 'application/xml');
            $response->headers->set('Content-Disposition', 'attachment; filename="facebook-catalog.xml"');
            
            return $response;
        } catch (\Exception $e) {
            // Log l'erreur mais ne la montre pas aux utilisateurs
            error_log('Erreur lors de la génération du catalogue XML: ' . $e->getMessage());
            throw new ServiceUnavailableHttpException(null, 'Le catalogue XML est temporairement indisponible.');
        }
    }
    
    #[Route('/facebook-catalog.csv', name: 'app_facebook_catalog_csv')]
    public function facebookCatalogCsv(): Response
    {
        $products = $this->productRepository->findAll();
        
        $headers = [
            'id',
            'title',
            'description',
            'link',
            'image_link',
            'additional_image_link',
            'availability',
            'price',
            'brand',
            'condition',
            'google_product_category',
            'product_type',
            'size',
        ];
        
        $csv = fopen('php://temp', 'w');
        fputcsv($csv, $headers);
        
        foreach ($products as $product) {
            if ($product->getStock() > 0 && count($product->getProductImages()) > 0) {
                $row = [];
                
                // ID unique du produit
                $row[] = $product->getId();
                
                // Titre du produit
                $row[] = $product->getName();
                
                // Description
                $row[] = $product->getDescription() ?: $product->getName();
                
                // Lien vers la page produit
                $row[] = $this->generateUrl('app_product_show_customer', [
                    'id' => $product->getId()
                ], UrlGeneratorInterface::ABSOLUTE_URL);
                
                // Image principale
                $mainImage = $product->getProductImages()->first();
                if ($mainImage) {
                    $imageUrl = $this->generateUrl('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL) . 
                                'uploads/' . $mainImage->getImagePath();
                    $row[] = $imageUrl;
                    
                    // Images additionnelles (prendre la 2ème image si disponible)
                    $additionalImages = $product->getProductImages()->slice(1, 1);
                    if (count($additionalImages) > 0) {
                        $additionalImageUrl = $this->generateUrl('app_home', [], UrlGeneratorInterface::ABSOLUTE_URL) . 
                                            'uploads/' . $additionalImages[0]->getImagePath();
                        $row[] = $additionalImageUrl;
                    } else {
                        $row[] = '';
                    }
                } else {
                    $row[] = '';
                    $row[] = '';
                }
                
                // Disponibilité
                $row[] = $product->getStock() > 0 ? 'in stock' : 'out of stock';
                
                // Prix
                $row[] = $product->getPrice() . ' FCFA';
                
                // Marque
                $row[] = $product->getBrand() ?: '';
                
                // Condition
                $row[] = 'new';
                
                // Catégorie Google
                $categories = $product->getSubCategories();
                if ($categories->count() > 0) {
                    $row[] = $categories->first()->getCategory()->getName();
                    $row[] = $categories->first()->getName();
                } else {
                    $row[] = '';
                    $row[] = '';
                }
                
                // Tailles si disponibles
                if ($product->getTaille() && is_array($product->getTaille()) && count($product->getTaille()) > 0) {
                    $row[] = implode(', ', $product->getTaille());
                } else {
                    $row[] = '';
                }
                
                fputcsv($csv, $row);
            }
        }
        
        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);
        
        $response = new Response($content);
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="facebook-catalog.csv"');
        
        return $response;
    }
} 