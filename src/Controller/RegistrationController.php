<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationForm;
use App\Security\SecurityControllerAuthenticator;
use App\Services\MobileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request                     $request,
        MobileService               $mobileService,
        UserPasswordHasherInterface $userPasswordHasher,
        Security                    $security,
        EntityManagerInterface      $entityManager): Response
    {
        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }
        $user = new User();
        $form = $this->createForm(RegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $password = $form->get('plainPassword')->getData();
            $confirmPassword = $form->get('confirmPassword')->getData();

            if ($password !== $confirmPassword) {
                // Ajoute une erreur et empêche l'enregistrement
                $form->get('confirmPassword')->addError(new FormError("Passwords don't match"));
            } else {
                // Encode le mot de passe
                $user->setPassword(
                    $userPasswordHasher->hashPassword($user, $password)
                );

                // Default role
                if (empty($user->getRoles())) {
                    $user->setRoles(['ROLE_USER']);
                }
                $user->setAdmin(false);
                $user->setActive(true);

                $entityManager->persist($user);
                $entityManager->flush();

                // Authentifie l'utilisateur après inscription
                $security->login($user, SecurityControllerAuthenticator::class, 'main');
                return $this->redirectToRoute('app_main');

            }
        }
        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}
