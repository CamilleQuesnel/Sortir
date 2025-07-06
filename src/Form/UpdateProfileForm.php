<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Hangout;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;


class UpdateProfileForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo')

            ->add('plainPassword', PasswordType::class, [
                'label' => 'Password : ',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                    new Regex([
                        'pattern' => '/(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>]).{8,}/',
                        'message' => 'Your password must include at least one uppercase letter, one lowercase letter, one digit and one special character',
                    ]),
                ],
            ])


            ->add('confirmPassword', PasswordType::class, [
                'label' => 'Confirm Password : ',
                'mapped' => false,
                'required' => false,
                'attr' => ['placeholder' => 'Password confirmation'],
            ])


            ->add('firstName')
            ->add('lastName')
            ->add('mail')
            ->add('phoneNumber')
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
