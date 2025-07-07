<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Hangout;
use App\Entity\Spot;
use App\Entity\Status;
use App\Entity\User;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateHangoutForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'] ?? null;
        $builder

            ->add('name', TextType::class, [
                'label' => 'Nom de la sortie',
                'attr' => ['class' => 'btn-action']
            ])
            ->add('startingDate',  DateType::class, [
                'label' => 'Date et heure de la sortie'
            ])

            ->add('registrationDeadline', DateType::class, [
                'label' => 'Date limite d\'inscription'
            ])
            ->add('duration', NumberType::class, [
                'label' => 'Durée'
            ])
            ->add('nbInscriptionsMax',  NumberType::class, [
                'label' => 'Nombre de places'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description et infos'
            ])

//            ->add('organizer', TextType::class, [
//                'mapped' => false,
//                'data' => $options['user'] ? $options['user']->getPseudo() : '',
//                'disabled' => true,
//                'label' => 'Organisateur',
//                'attr' => ['class' => 'btn-action']
//            ])
            ->add('spot', EntityType::class, [
                'class' => Spot::class,
                'choice_label' => 'name',
                'placeholder' => 'Choisir un lieu',
                'label' => 'Lieu',
                'attr' => [
                    'id' => 'spot-select'
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
                'attr' => ['class' => 'btn-action']
            ])

            ->add('publish', SubmitType::class, [
                'label' => 'Créer une sortie',
                'attr' => ['class' => 'btn-action']
            ])


            ;

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hangout::class,
            'user' => null,
            'csrf_protection' => true,
        ]);

    }
}
