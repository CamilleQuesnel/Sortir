<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HangoutWithSpotUpdateForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('hangout', CreateHangoutForm::class, [
                'label' => false,
            ])
            ->add('spot', SpotForm::class, [
                'label' => false,
            ])
            ->add('publish', SubmitType::class, [
                'label' => 'Publier la sortie',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, // ← on passe un tableau, pas une seule entité
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
            'csrf_token_id'   => 'hangout_update',
        ]);
    }
}
