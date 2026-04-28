<?php

namespace App\Service;

use App\Entity\Order;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class PrintService 
{  
    private $twig;
    
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }
    
    public function printOrder(Order $order): Response
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($pdfOptions);
        
        $html = $this->twig->render('admin/dashboard/order/print.html.twig', [
            'order' => $order
        ]);
        
        $dompdf->loadHtml($html);
        $dompdf->render();
        
        $response = new Response($dompdf->output());
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="order'.$order->getFirstname().'.pdf"');
        
        return $response;
    }
}