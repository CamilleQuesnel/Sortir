<?php

namespace App\Controller;


use App\Form\ProfileImageFormType;
use App\Form\UpdateProfileForm;
use App\Repository\UserRepository;
use App\Services\MobileService;
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
    #[Route('profile/{id}', name: 'app_profile_id', requirements: ['id' => '\d+'], methods: ['GET'])]
//rajout du requirement pour éviter le conflit de route
    public function profileId(
        int            $id,
        UserRepository $userRepository,
        Request        $request,
        MobileService  $mobileService,
    ): Response
    {
        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }
        $user = $userRepository->find($id);
        return $this->render('profile/show-profile.html.twig', ['user' => $user]);
    }

    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    public function profile(
        Request       $request,
        MobileService $mobileService,
    ): Response
    {
        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }
        $user = $this->getUser();
        $imageForm = $this->createForm(ProfileImageFormType::class);

        return $this->render('profile/index.html.twig', [
            'profileImageForm' => $imageForm,
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

        $form = $this->createForm(ProfileImageFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move($ImagesDirectory, $newFilename);
                    $user->setImage($newFilename);

                    $entityManager->persist($user);
                    $entityManager->flush();

                    $this->addFlash('success', 'Image mise à jour avec succès !');
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors du téléchargement de l\'image, veuillez réessayer');
                }
            }
        }
        return $this->redirectToRoute('app_profile');
    }

    #[Route('/profile/update', name: 'app_profile_update', methods: ['POST', 'GET'])]
    public function profileUpdate(
        Request                                                         $request,
        EntityManagerInterface                                          $entityManager,
        SluggerInterface                                                $slugger,
        #[Autowire('%kernel.project_dir%/public/upload/images')] string $ImagesDirectory,
        UserPasswordHasherInterface                                     $passwordHasher
    ): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Utilisateur non connecté !');
        }

        $updateProfileForm = $this->createForm(UpdateProfileForm::class, $user);
        $profileImageForm = $this->createForm(ProfileImageFormType::class, $user);

        $updateProfileForm->handleRequest($request);
        dump($updateProfileForm->getData());
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
                    $this->addFlash('danger', 'Erreur lors du téléchargement de l\'image, veuillez réessayer');
                    return $this->redirectToRoute('app_profile_update');
                }

                $entityManager->flush();
                $this->addFlash('success', 'Image téléchargée !');
                return $this->redirectToRoute('app_profile_update');
            }
        }

        // Traitement du reste du profil
        if ($updateProfileForm->isSubmitted()) {
            dump($updateProfileForm->isValid());
            dump($updateProfileForm->getErrors(true, false));

            if ($updateProfileForm->isValid()) {
                // Mot de passe
                $plainPassword = $updateProfileForm->get('plainPassword')->getData();
                $confirmPassword = $updateProfileForm->get('confirmPassword')->getData();

                if ($plainPassword !== $confirmPassword) {
                    $updateProfileForm->get('confirmPassword')->addError(new FormError('Les deux mots de passe ne correspondent pas, merci de vérifier votre saisie.'));

                } else {
                    if ($plainPassword) {
                        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                        $user->setPassword($hashedPassword);
                    }
                    $entityManager->persist($user);
                    $entityManager->flush();
                    $this->addFlash('success', 'Profil mis à jour !');
                    return $this->redirectToRoute('app_profile_update');
                }
            } else {
                $this->addFlash('warning', 'Mise à jour échouée, merci de vérifier vos informations.');
            }

            return $this->redirectToRoute('app_profile_update');
        }

        return $this->render('profile/update.html.twig', [
            'updateProfileForm' => $updateProfileForm,
            'profileImageForm' => $profileImageForm,
            'user' => $user,
        ]);
    }
}
