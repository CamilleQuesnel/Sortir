<?php

namespace App\Controller;


use App\Form\ProfileImageFormType;
use App\Form\UpdateProfileForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ProfileController extends AbstractController
{

    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    public function profile(Request $request): Response
    {
        $user = $this->getUser();
        $imageForm = $this->createForm(ProfileImageFormType::class);

        return $this->render('profile/index.html.twig', [
            'profileImageForm' => $imageForm->createView(),
            'user' => $user
        ]);
    }


    #[Route('/profile/update-image', name: 'app_profile_update_image', methods: ['POST'])]
    public function updateImage(
        Request                                                         $request,
        EntityManagerInterface                                          $entityManager,
        SluggerInterface                                                $slugger,
        #[Autowire('%kernel.project_dir%/public/upload/images')] string $ImagesDirectory
    ): Response
    {
        $user = $this->getUser();


        $form = $this->createForm(ProfileImageFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $ImagesDirectory, $newFilename
                    );
                    $user->setImage($newFilename);

                } catch (FileException $e) {
                    $this->addFlash('danger', 'Error, picture can\'t be loaded');
                    return $this->redirectToRoute('app_profile');
                }

                $user->setImage($newFilename);
                $entityManager->persist($user);
                $entityManager->flush();
//                dd($user->getImage());
                $this->addFlash('success', 'Image mise à jour avec succès !');
            }
        }

        return $this->redirectToRoute('app_profile');
    }


    #[Route('/profile/update', name: 'app_profile_update', methods: ['POST', 'GET'])]
    public function profileUpdate(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        #[Autowire('%kernel.project_dir%/public/upload/images')] string $ImagesDirectory,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $this->getUser();

        $updateProfileForm = $this->createForm(UpdateProfileForm::class, $user);
        $profileImageForm = $this->createForm(ProfileImageFormType::class, $user);

        $updateProfileForm->handleRequest($request);
        $profileImageForm->handleRequest($request);

        // Traitement image (comme avant)
        if ($profileImageForm->isSubmitted() && $profileImageForm->isValid()) {
            $imageFile = $profileImageForm->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move($ImagesDirectory, $newFilename);
                    $user->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Error: image not uploaded');
                    return $this->redirectToRoute('app_profile_update');
                }

                $entityManager->flush();
                $this->addFlash('success', 'Avatar updated!');
                return $this->redirectToRoute('app_profile_update');
            }
        }

        // Traitement du reste du profil
        if ($updateProfileForm->isSubmitted()) {
            if ($updateProfileForm->isValid()) {
                // Mot de passe
                $plainPassword = $updateProfileForm->get('plainPassword')->getData();
                $confirmPassword = $updateProfileForm->get('confirmPassword')->getData();

                if ($plainPassword !== $confirmPassword) {
                    $updateProfileForm->get('confirmPassword')->addError(new FormError('Passwords do not match.'));

                } else {
                    if ($plainPassword) {
                        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                        $user->setPassword($hashedPassword);
                    }

                    $entityManager->flush();
                    $this->addFlash('success', 'Profile updated!');
                    return $this->redirectToRoute('app_profile_update');
                }
            } else {
                $this->addFlash('warning', 'Update failed. Please check your information.');
            }

            return $this->redirectToRoute('app_profile_update');
        }


        return $this->render('profile/update.html.twig', [
            'updateProfileForm' => $updateProfileForm,
            'profileImageForm' => $profileImageForm,
        ]);
    }


}
