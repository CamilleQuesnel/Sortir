<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo', TextType::class, [
                'attr' => ['placeholder' => 'Pseudo'],
                'required' => true,
                'label' => 'Pseudo : ',
                'trim' => true
            ])

            ->add('firstname', TextType::class, [
                'attr' => ['placeholder' => 'Firstname'],
                'required' => true,
                'label' => 'Firstname : ',
                'trim' => true
            ])

            ->add('lastname', TextType::class, [
                'attr' => ['placeholder' => 'Lastname'],
                'required' => true,
                'label' => 'Lastname : ',
                'trim' => true
            ])

            ->add('mail', EmailType::class, [
                'attr' => ['placeholder' => 'Email'],
                'required' => true,
                'label' => 'Email : ',
                'trim' => true
            ])

            ->add('confirmPassword', PasswordType::class, [
                'label' => 'Confirm Password : ',
                'mapped' => false,
                'constraints' => [new NotBlank()],
                'attr' => ['placeholder' => 'Password confirmation'],
                'required' => true,
            ])

            ->add('phoneNumber', TextType::class,  [
                'attr' => ['placeholder' => '02 98 07 21 16 '],
                'required' => true,
                'label' => 'Phone Number : ',
                'trim' => true
            ])

            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'name',
                'placeholder' => 'Choose a campus',
                'label' => false, // ✅ pas de label affiché
            ])

            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label' => 'Password : ',
                'attr' => [
                    'autocomplete' => 'new-password',
                    'placeholder' => 'Password'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        'max' => 4096,
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
