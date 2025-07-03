<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Spot;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpotForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('address')
            ->add('latitude')
            ->add('longitude')
            ->add('zipCode', TextType::class, [
                'mapped' => false,
                'label' => 'Zip code : '
            ])
            ->add('cityName', TextType::class, [
                'mapped' => false,
                'label' => 'City : '
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Spot::class,
        ]);
    }
}
