<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\User;
use App\Form\CityForm;
use App\Form\CreateUserFormType;
use App\Repository\CityRepository;
use App\Services\MobileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Csrf\CsrfToken;


final class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin')]
    #[IsGranted('ROLE_ADMIN')]

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

    #[Route('/admin/list-users', name: 'admin_list_users')]
    #[IsGranted('ROLE_ADMIN')]
    public function listUsers(Request $request, EntityManagerInterface $entityManager): Response
    {
        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('admin/list_users.html.twig', ['users' => $users]);
    }

    #[Route('/admin/create-user', name: 'admin_create_user')]
    #[IsGranted('ROLE_ADMIN')]
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

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();

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
            return $this->redirectToRoute('admin_create_user');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('error', 'Le formulaire contient des erreurs !');

            $form->get('pseudo')->addError(new FormError('Merci de rentrer un vrai pseudo !'));
        }

        return $this->render('admin/create-user.html.twig', [
            'createUser' => $form,
        ]);
    }
    #[Route('/admin/user/{id}/desactivate', name: 'admin_user_deactivate')]
    public function deactivateUser(User $user, EntityManagerInterface $em): Response
    {
        $user->setActive(false);
        $em->flush();
        $this->addFlash('success', 'Utilisateur désactivé avec succès.');
        return $this->redirectToRoute('admin_list_users');
    }

    #[Route('/admin/cites', name: 'admin_cities', methods: ['GET', 'POST'])]
    public function cites(Request $request, CityRepository $cityRepository): Response
    {
        $cities = $cityRepository->findBy(['isActive' => true], ['name' => 'ASC']);

        return  $this->render('admin/cites.html.twig', ['cities' => $cities]);
    }

    #[Route('/admin/city/{id}/delete', name: 'admin_city_soft_delete', methods: ['POST'])]
    public function softDeleteCity(City $city, EntityManagerInterface $em, Request $request): Response
    {
        if ($this->isCsrfTokenValid('delete'.$city->getId(), $request->request->get('_token'))) {
            $city->setIsActive(false);
            $em->flush();
            $this->addFlash('success', 'La ville a été supprimée.');
        }

        $city->setIsActive(false);
        $em->flush();

        $this->addFlash('success', 'La ville a été supprimée.');

        return $this->redirectToRoute('admin_cities');
    }

    #[Route('/admin/city/add', name: 'admin_city_add')]
    public function addCity(Request $request, EntityManagerInterface $em, CityRepository $cityRepository): Response
    {
        $city = new City();
        $form = $this->createForm(CityForm::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existing = $cityRepository->findOneBy([
                'name' => $city->getName(),
                'zipCode' => $city->getZipCode(),
            ]);

            if ($existing) {
                if (!$existing->isActive()) {
                    $existing->setIsActive(true);
                    $em->flush();
                    $this->addFlash('success', 'Ville réactivée avec succès.');
                } else {
                    $this->addFlash('warning', 'Cette ville est déjà active.');
                }
            } else {
                $em->persist($city);
                $em->flush();
                $this->addFlash('success', 'Ville ajoutée avec succès.');
            }

            return $this->redirectToRoute('admin_cities');
        }

        return $this->render('admin/city_add.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/admin/city/{id}/edit', name: 'admin_city_edit')]
    public function editCity(City $city, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CityForm::class, $city);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'La ville a été mise à jour.');
            return $this->redirectToRoute('admin_cities');
        }

        return $this->render('admin/city_edit.html.twig', [
            'form' => $form,
            'city' => $city,
        ]);
    }


    #[Route('/admin/campus', name: 'admin_campus', methods: ['GET', 'POST'])]
    public function campus(Request $request, EntityManagerInterface $entityManager): Response
    {
        $campus = $entityManager->getRepository(Campus::class)->findAll();

        return  $this->render('admin/campus.html.twig', ['campus' => $campus]);
    }
    #[Route('/admin/user/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(Request $request, User $user, EntityManagerInterface $em): Response
    {
        $token = new CsrfToken('delete-user-' . $user->getId(), $request->request->get('_token'));

        if (!$this->isCsrfTokenValid($token->getId(), $token->getValue())) {
            throw $this->createAccessDeniedException('CSRF token invalide');
        }

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        return $this->redirectToRoute('admin_list_users');
    }

}

