<?php

namespace App\Form;

use App\Entity\Hangout;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateHangoutForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('hangout', CreateHangoutForm::class, [
                'data_class' => Hangout::class,
                'user' => $options['user'],
                'label' => false,
            ])
            ->add('spot', SpotForm::class, [
                'label' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'user' => null,
        ]);
    }
}

