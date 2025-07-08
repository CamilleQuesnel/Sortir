<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\CreateUserFormType;
use App\Form\RegistrationForm;
use App\Services\MobileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin')]
    public function index(
        Request $request,
        MobileService $mobileService,
    ): Response
    {
        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }
        return $this->render('admin/index.html.twig', []);
    }


    #[Route('/admin/create-user', name: 'create_user')]
    public function createUser(
        Request $request,
        MobileService $mobileService,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {

        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }

        $user = new User();
        $form = $this->createForm(CreateUserFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();

            if ($plainPassword !== $confirmPassword) {
                $form->get('confirmPassword')->addError(new FormError('Les mots de passe ne correspondent pas.'));
            }

            if ($form->isValid()) {
                foreach ($form->getErrors(true) as $error) {
                    dump($error->getMessage());
                }
                // Hash du mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);

                // Admin ?
                $isAdmin = $form->get('isAdmin')->getData();
                $user->setRoles($isAdmin ? ['ROLE_ADMIN'] : ['ROLE_USER']);
                $user->setAdmin($isAdmin ?? false);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Utilisateur créé avec succès !');
                return $this->redirectToRoute('create_user');
            }

            $this->addFlash('danger', 'Merci de corriger les erreurs du formulaire.');
        }

        return $this->render('admin/create-user.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

}
