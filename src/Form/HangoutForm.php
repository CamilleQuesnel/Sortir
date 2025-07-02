<?php

namespace App\Form;

use App\DTO\HangoutFilterDTO;
use App\Entity\Campus;
use App\Entity\Hangout;
use App\Entity\Spot;
use App\Entity\Status;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HangoutForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name',
                'label' => 'Site',
                'placeholder' => 'Tous les sites',
                'required' => false
            ])
            ->add('outputNameContains', TextType::class, [
//                'mapped' => false,
                'label' => 'Le nom de la sortie contient :',
                'required' => false,
                'attr' => ['placeholder' => 'search']
            ])
            ->add('dateFrom', DateType::class, [
//                'mapped' => false,
                'label' => 'Entre',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'datepicker']
            ])
            ->add('dateTo', DateType::class, [
//                'mapped' => false,
                'label' => 'et',
                'required' => false,
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'datepicker']
            ])
            ->add('isOrganizer', CheckboxType::class, [
//                'mapped' => false,
                'label' => 'Sorties dont je suis l’organisateur/trice',
                'required' => false
            ])
            ->add('isRegistered', CheckboxType::class, [
//                'mapped' => false,
                'label' => 'Sorties auxquelles je suis inscrit/e',
                'required' => false
            ])
            ->add('isNotRegistered', CheckboxType::class, [
//                'mapped' => false,
                'label' => 'Sorties auxquelles je ne suis pas inscrit/e',
                'required' => false
            ])
            ->add('isPast', CheckboxType::class, [
//                'mapped' => false,
                'label' => 'Sorties passées',
                'required' => false
            ])
            ->add('search', SubmitType::class, [
                'label' => 'Rechercher',
                'attr' => ['class' => 'btn btn-primary']
            ]);
        ;
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => HangoutFilterDTO::class,
        ]);
    }
}
