<?php

declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProfileImageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('imageFile', FileType::class, [
            'label' => false,
            'mapped' => false,
            'required' => false,
            'attr' => [
                'class' => 'hidden-file-input',
                'accept' => 'image/*',
                'id' => 'avatar-upload'
            ],
            'constraints' => [
                new File([
                    'maxSize' => '2M',
                    'maxSizeMessage' => 'L\'image ne doit pas dépasser 2 Mo.',
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/png',
                        'image/webp',
                        'image/gif',
                    ],
                    'mimeTypesMessage' => 'Formats autorisés : JPG, PNG, WEBP ou GIF.',
                    'uploadErrorMessage' => 'Une erreur est survenue lors de l\'upload du fichier.',
                    'notReadableMessage' => 'Le fichier n\'a pas pu être lu. Vérifiez sa validité.',
                ])
            ],
        ]);
    }


        public function configureOptions(OptionsResolver $resolver)
    {
    }
}
