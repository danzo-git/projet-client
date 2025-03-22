<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\SubCategory;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ProductUpdateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('price')
            ->add('taille', ChoiceType::class, [
                'choices' => [
                    'Small' => 'S',
                    'Medium' => 'M',
                    'Large' => 'L',
                    'Extra Large' => 'XL',
                    'XXL' => 'XXL',
                    '32' => '32',
                    '34' => '34',
                    '36' => '36',
                    '38' => '38',
                    '40' => '40',
                    '42' => '42',
                    '44' => '44',
                    '46' => '46',
                ],
                'placeholder' => 'Choisissez une taille', // Optionnel : texte par défaut
                'multiple' => true, // Permet la sélection multiple (retourne un tableau)
                'expanded' => false, // Affiche un menu déroulant
                'required' => false, // Optionnel : si le champ n'est pas obligatoire
            ])
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
            // ->add('stock')
            ->add('subCategories', EntityType::class, [
                'class' => SubCategory::class,
                'choice_label' => 'id',
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
