<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\SubCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('price')
            ->add('productImages', CollectionType::class, [
                'entry_type' => ProductImageType::class,
                'allow_add' => true,      // Permet d'ajouter des champs dynamiquement 
                'allow_delete' => true,   // Permet de supprimer des champs
                'by_reference' => false,  // Important pour que addProductImage() et removeProductImage() soient appelés
                'prototype' => true,      // Génère un prototype pour JS
                'label' => 'Images du produit',
                'required' => false,
                'entry_options' => [
                    'label' => false,
                ],
            ])
            ->add('taille')
            ->add('stock')
            ->add('subCategories', EntityType::class, [
                'class' => SubCategory::class,
                'choice_label' => 'name',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}