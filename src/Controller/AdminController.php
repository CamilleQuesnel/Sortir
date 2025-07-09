<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\Hangout;
use App\Entity\Status;
use App\Entity\User;
use App\Form\CampusForm;
use App\Form\CityForm;
use App\Form\CreateUserFormType;
use App\Repository\CampusRepository;
use App\Repository\CityRepository;
use App\Repository\HangoutRepository;
use App\Services\MobileService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
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
        Request       $request,
        MobileService $mobileService,
    ): Response
    {
        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }
        return $this->render('admin/index.html.twig', []);
    }


    //    ###################### GESTION USERS ######################
    #[Route('/admin/list-users', name: 'admin_list_users')]
    #[IsGranted('ROLE_ADMIN')]
    public function listUsers(Request $request, EntityManagerInterface $entityManager, MobileService $mobileService
    ): Response
    {
        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }
        $users = $entityManager->getRepository(User::class)->findAll();

        return $this->render('admin/list_users.html.twig', ['users' => $users]);
    }

    #[Route('/admin/create-user', name: 'admin_create_user')]
    #[IsGranted('ROLE_ADMIN')]
    public function createUser(
        Request                     $request,
        MobileService               $mobileService,
        EntityManagerInterface      $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {

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
    #[IsGranted('ROLE_ADMIN')]
    public function deactivateUser(User $user, EntityManagerInterface $em, Request $request, MobileService $mobileService
    ): Response
    {
        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }
        $user->setActive(false);
        $em->flush();
        $this->addFlash('success', 'Utilisateur désactivé avec succès.');
        return $this->redirectToRoute('admin_list_users');
    }


    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    #[Route('/admin/user/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(Request $request, User $user, EntityManagerInterface $em, HangoutRepository $hangoutRepository): Response
    {
        $token = new CsrfToken('delete-user-' . $user->getId(), $request->request->get('_token'));

        if (!$this->isCsrfTokenValid($token->getId(), $token->getValue())) {
            throw $this->createAccessDeniedException('CSRF token invalide');
        }

        //todo suppr le participant des sorties en query
        $user = $em->find(User::class, $user->getId());

//        foreach ($user->getHangouts() as $userToDelete) {
//            $userToDelete->removeParticipant($user);
//        }
        //todo annuler la sortie de l'utisateur supprimé

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        return $this->redirectToRoute('admin_list_users');
    }

//    ###################### CITIES ######################

    #[Route('/admin/cites', name: 'admin_cities', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function cites(
        Request        $request,
        CityRepository $cityRepository,
        MobileService  $mobileService
    ): Response
    {

        $search = $request->query->get('search');

        if ($search) {
            $cities = $cityRepository->createQueryBuilder('c')
                ->where('c.isActive = true')
                ->andWhere('c.name LIKE :search')
                ->setParameter('search', '%' . $search . '%')
                ->orderBy('c.name', 'ASC')
                ->getQuery()
                ->getResult();
        } else {
            $cities = $cityRepository->findBy(['isActive' => true], ['name' => 'ASC']);
        }

        return $this->render('admin/cites.html.twig', [
            'cities' => $cities,
        ]);

        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }
        $cities = $cityRepository->findBy(['isActive' => true], ['name' => 'ASC']);

        return $this->render('admin/cites.html.twig', ['cities' => $cities]);

    }

    #[Route('/admin/city/{id}/delete', name: 'admin_city_soft_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function softDeleteCity(
        City                   $city,
        EntityManagerInterface $em,
        Request                $request,
        MobileService          $mobileService
    ): Response
    {
        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }

        if ($this->isCsrfTokenValid('delete' . $city->getId(), $request->request->get('_token'))) {
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
    #[IsGranted('ROLE_ADMIN')]
    public function addCity(Request $request, EntityManagerInterface $em, CityRepository $cityRepository, MobileService $mobileService
    ): Response
    {
        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }
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
    #[IsGranted('ROLE_ADMIN')]
    public function editCity(City $city, Request $request, EntityManagerInterface $em, MobileService $mobileService
    ): Response
    {
        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }
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


    //    ###################### CAMPUS ######################

    #[Route('/admin/campus', name: 'admin_campus', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function campus(Request $request, EntityManagerInterface $entityManager, MobileService $mobileService
    ): Response
    {


        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }

        $campus = $entityManager->getRepository(Campus::class)->findBy(['isActive' => true]);

        $search = $request->query->get('search');

        $campusRepository = $entityManager->getRepository(Campus::class);

        if ($search) {
            $queryBuilder = $campusRepository->createQueryBuilder('c');
            $queryBuilder->where('c.isActive = true')
                ->andWhere('c.name LIKE :search')
                ->setParameter('search', '%' . $search . '%');
            $campus = $queryBuilder->getQuery()->getResult();
        } else {
            $campus = $campusRepository->findBy(['isActive' => true]);
        }

        return $this->render('admin/campus.html.twig', [
            'campus' => $campus,
        ]);

    }

    #[Route('/admin/campus/add', name: 'admin_campus_add')]
    #[IsGranted('ROLE_ADMIN')]
    public function addCampus(Request                $request,
                              EntityManagerInterface $em,
                              CampusRepository       $campusRepository,
                              MobileService          $mobileService
    ): Response
    {
        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }
        $campus = new Campus();
        $form = $this->createForm(CampusForm::class, $campus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existing = $campusRepository->findOneBy([
                'name' => $campus->getName(),
            ]);

            if ($existing) {
                if (!$existing->isActive()) {
                    $existing->setIsActive(true);
                    $em->flush();
                    $this->addFlash('success', 'Campus réactivé avec succès.');
                } else {
                    $this->addFlash('warning', 'Ce campus est déjà actif.');
                }
            } else {
                $em->persist($campus);
                $em->flush();
                $this->addFlash('success', 'Campus ajouté avec succès.');
            }

            return $this->redirectToRoute('admin_campus');
        }

        return $this->render('admin/campus_add.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/admin/campus/{id}/edit', name: 'admin_campus_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function editCampus(Campus $campus, Request $request, EntityManagerInterface $em, MobileService $mobileService
    ): Response
    {
        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }
        $form = $this->createForm(CampusForm::class, $campus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Le campus a été mis à jour.');
            return $this->redirectToRoute('admin_campus');
        }

        return $this->render('admin/campus_edit.html.twig', [
            'form' => $form,
            'campus' => $campus,
        ]);
    }

    #[Route('/admin/campus/{id}/delete', name: 'admin_campus_soft_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function softDeleteCampus(Campus                 $campus,
                                     HangoutRepository      $hangoutRepository,
                                     EntityManagerInterface $em,
                                     Request                $request, MobileService $mobileService
    ): Response
    {
        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }
        if ($this->isCsrfTokenValid('delete' . $campus->getId(), $request->request->get('_token'))) {
            $campus->setIsActive(false);
            $em->flush();
            $this->addFlash('success', 'Le campus a été supprimé.');
        }

        $campus->setIsActive(false);

        // Désactiver tous les utilisateurs liés
        $campusId = $campus->getId();
        $users = $em->getRepository(User::class)->findAll();

        foreach ($users as $user) {
            if ($user->getCampus()->getId() === $campusId) {
                $user->setActive(false);
                $hangoutWithCampusDeleted = $em->getRepository(Hangout::class)->findBy(['campus' => $campus]);

                foreach ($hangoutWithCampusDeleted as $h) {
                    $annuleeStatus = $em->getRepository(Status::class)->findOneBy(['label' => 'Annulée']);
                    $h->setStatus($annuleeStatus);
                }
            };
        }

        $em->flush();

        $this->addFlash('success', 'Le campus a été supprimé.');

        return $this->redirectToRoute('admin_campus');
    }



    #[Route('/admin/user/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(Request $request, User $user, EntityManagerInterface $em,MobileService $mobileService
    ): Response
    {
        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }
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

