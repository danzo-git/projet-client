<?php

// src/Service/EmailService.php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;
use Twig\Environment;
use App\Entity\Order;

class EmailService
{
    private $mailer;
    private $twig;
    private $senderEmail;
    private $entityManager;
    
    public function __construct(MailerInterface $mailer, Environment $twig,  EntityManagerInterface $entityManager)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->entityManager = $entityManager;
    }
    
    public function sendOrderConfirmation( $order)
    {
         // Par exemple avec Doctrine :
    $this->entityManager->refresh( $order);
    foreach ($order->getOrderProducts() as $orderProduct) {
        $orderProduct->getProduct()->getProductImages()->initialize();
    }
        $email = (new Email())
            ->from(new Address('noreply@rtcstore225.com', 'Votre Boutique'))
            ->to($order->getEmail())
            ->subject('Confirmation de votre commande #' . $order->getId())
            ->html(
                $this->twig->render('mail/mailConfirmationOrder.html.twig', [
                    'order' => $order
                ])
            );
            
        $this->mailer->send($email);
    }
}