<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Hangout;
use App\Entity\Spot;
use App\Entity\Status;
use App\Entity\User;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateHangoutForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'] ?? null;
        $builder

            ->add('name')
            ->add('startingDate')
            ->add('registrationDeadline')
            ->add('duration')
            ->add('nbInscriptionsMax')
            ->add('description')

            ->add('organizer', TextType::class, [
                'mapped' => false,
                'data' => $options['user'] ? $options['user']->getPseudo() : '',
                'disabled' => true,
                'label' => 'Organizer'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'attr' => ['class' => 'btn btn-secondary']
            ])

            ->add('publish', SubmitType::class, [
                'label' => 'Publish hangout',
                'attr' => ['class' => 'btn btn-primary']
            ])

            ->add('spot', EntityType::class, [
                'class' => Spot::class,
                'choice_label' => 'name',
                'placeholder' => 'Choose a spot',
                'attr' => [
                    'id' => 'spot-select'
                ]
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
