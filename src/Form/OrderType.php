<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Order;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        
        $builder
        ->add('firstName', null, [
            'label' => false,
            'attr' => ['autocomplete' => 'given-name']
        ])
        ->add('lastName', null, [
            'label' => false,
            'attr' => ['autocomplete' => 'family-name']
        ])
        ->add('email', null, [
            'label' => false,
            'attr' => ['autocomplete' => 'email']
        ])
        ->add('phone', TelType::class, [
            'label' => false,
            'attr' => ['autocomplete' => 'tel']
        ])
            ->add('address')
            // ->add('createdAt', null, [
            //     'widget' => 'single_text',
            // ])
            ->add('city', EntityType::class, [
                'class' => City::class,
                'choice_label' => 'name',
            ])
            ->add('additionalAddress',)
            ->add('paymentOnDelivery')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
