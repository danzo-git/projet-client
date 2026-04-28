<?php


// src/EntityListener/OrderEntityListener.php
namespace App\EntityListener;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use App\Entity\Order;
use DateTimeImmutable;

class OrderEntityListener
{
    #[AsEntityListener(event: 'prePersist', entity: Order::class)]
    public function prePersist(Order $order, PrePersistEventArgs $event): void
    {
        if ($order->getCreatedAt() === null) {
            $order->setCreatedAt(new DateTimeImmutable());
        }
    }
}