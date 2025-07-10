<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateHangoutForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('hangout', CreateHangoutForm::class, [
                'label' => false,
                'user' => $options['user'], // tu peux passer l'utilisateur à l'intérieur si besoin
            ])
            ->add('spot', SpotForm::class, [
                'label' => false
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UpdateHangoutDTO::class,
            'user' => null,
        ]);
    }
}


