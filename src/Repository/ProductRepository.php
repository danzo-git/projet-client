<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Dom\Entity;

/**
 * @extends ServiceEntityRepository<Product>
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    //    /**
    //     * @return Product[] Returns an array of Product objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Product
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
    * @return Product[] Returns an array of Bien objects   
     */
   public function find10Biens(EntityManager $entityManager): array
   {
    $qb = $entityManager->createQueryBuilder();
    $qb->select('p', 's', 'pi')
       ->from('App\Entity\Product', 'p')
       ->leftJoin('p.subCategories', 's')
       ->leftJoin('p.productImages', 'pi');
    
    $query = $qb->getQuery();
    $products = $query->getResult();
    return $products;
   }


   public function find3ProductsByRandomWithSubCategoryAndCategoryAndImagesAssociated(EntityManager $entityManager): array
   {
       $qb = $entityManager->createQueryBuilder();
       $qb->select('p', 's', 'pi')
          ->from('App\Entity\Product', 'p')
          ->leftJoin('p.subCategories', 's')
          ->leftJoin('p.productImages', 'pi')
          ->orderBy('RAND()')
          ->groupBy('p.id', 's.id', 'pi.id') // Groupement pour éviter les doublons
          ->setMaxResults(3)
         ;
       
       $query = $qb->getQuery();
       $products = $query->getResult();
       
       return $products;
   }
}
