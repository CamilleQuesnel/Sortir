<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Hangout;
use App\Entity\Spot;
use App\Entity\Status;
use App\Entity\User;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
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
                'label' => 'Organisateur'
            ])
            ->add('status', EntityType::class, [
                'class' => Status::class,
                'choice_label' => 'label',
            ])
            ->add('spot', EntityType::class, [
                'class' => Spot::class,
                'choice_label' => 'name'
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Hangout::class,
        ]);
        $resolver->setDefined('user');
    }
}
