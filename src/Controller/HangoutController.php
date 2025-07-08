<?php

namespace App\Controller;

use App\Entity\Hangout;
use App\Entity\Spot;
use App\Entity\User;
use App\Form\CreateHangoutForm;
use App\Form\HangoutForm;
use App\Form\HangoutWithSpotUpdateForm;
use App\Repository\CityRepository;
use App\Repository\HangoutRepository;
use App\Repository\SpotRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use App\Services\MobileService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/hangout', name: 'hangout_')]
final class HangoutController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET', 'POST'])]
    public function index(
        Request                $request,
        MobileService $mobileService,
        HangoutRepository      $hangoutRepository,
        StatusRepository       $statusRepository,
        UserRepository         $userRepository,
        EntityManagerInterface $entityManager
    ): Response
    {

        $user = $this->getUser();
        $userAgent = $request->headers->get('User-Agent');
        $isMobile = stripos($userAgent, 'Mobile');
        $hangouts = $hangoutRepository->findByFilters($user, $filters ?? []);
        //Verification date des sorties
        $today = new DateTimeImmutable('today');
        $nextMonth = $today->modify('+1 month');

        foreach ($hangouts as $sortie) {
            $label = $sortie->getStatus()->getLabel();

            if ($label !== 'Passée' && $sortie->getStartingDate() < $today) {
                $statusPassee = $statusRepository->findOneBy(['label' => 'Passée']);
                $sortie->setStatus($statusPassee);
            }
            if ($label === 'Passée' && $sortie->getStartingDate() < $nextMonth) {
                $statusArchivee = $statusRepository->findOneBy(['label' => 'Archivée']);
                $sortie->setStatus($statusArchivee);
            }
            if ($label !== 'Passée' && $sortie->getRegistrationDeadline() < $today) {
                $statusPassee = $statusRepository->findOneBy(['label' => 'Fermée']);
                $sortie->setStatus($statusPassee);
            }
            $entityManager->persist($sortie);

        }
        $entityManager->flush();
        $form = $this->createForm(HangoutForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
        }
        $hangoutWithCity = [];


        if ($mobileService->isMobile($request)) {
            $filters['isRegistered'] = true;
            $hangouts = $hangoutRepository->findByFilters($user, $filters ?? []);
        } else {
            $hangouts = $hangoutRepository->findByFilters($user, $filters ?? []);
        }

        foreach ($hangouts as $hangout) {
            $cityName = $hangout->getSpot()?->getCity()?->getName() ?? 'Inconnue';
            $hangoutWithCity[] =
                [
                    'hangout' => $hangout,
                    'city' => $cityName,
                ];
        }
        return $this->render('hangout/index.html.twig', [
            'hangoutWithCity' => $hangoutWithCity,
            'user' => $user,
            'form' => $form
        ]);
    }


    #[Route('/publish', name: 'publish', methods: ['GET', 'POST'])]
    public function publish(
        Request                $request,
        MobileService          $mobileService,
        EntityManagerInterface $entityManager,
        StatusRepository       $statusRepository
    ): Response
    {

        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }
        //transmission des spots
        $spots = $entityManager->getRepository(Spot::class)->findAll();


        $hangout = new Hangout();
        $createHangoutForm = $this->createForm(
            CreateHangoutForm::class,
            $hangout,
            ['user' => $this->getUser(),]);

        $createHangoutForm->handleRequest($request);
        if ($createHangoutForm->isSubmitted() && $createHangoutForm->isValid()) {

            //vérifie que la ville du spot sélectionné est bien activée
            if (!$hangout->getSpot()->getCity()->isActive()) {
                $this->addFlash('danger', 'Impossible de créer une sortie dans une ville désactivée.');
                return $this->redirectToRoute('hangout_publish');
            }

            $hangout->setOrganizer($this->getUser());

            $hangout->setCampus($this->getUser()->getCampus());

            if ($createHangoutForm->get('publish')->isClicked()) {
                $status = $statusRepository->findOneBy(['label' => 'Ouverte']);//quand l'organisateur clique sur publier la sortie
                $this->addFlash('success', "La sortie a bien été publiée.");
            } else {
                $status = $statusRepository->findOneBy(['label' => 'Créée']);//quand l'organisateur clique sur enregistrer elle est juste créée
                $this->addFlash('success', "La sortie a bien été enregistrée. Pensez à la publier.");
            }

            $hangout->setStatus($status);

            $entityManager->persist($hangout);
            $entityManager->flush();

            return $this->redirectToRoute('hangout_index');
        }

        return $this->render('hangout/publish.html.twig',
            [
                'createHangoutForm' => $createHangoutForm,
                'spots' => $spots,
            ]);
    }

    #[Route('/{id}', name: 'details', methods: ['GET', 'POST'])]
    public function details(
        int               $id,
        HangoutRepository $hangoutRepository,
    ): Response
    {
        $hangout = $hangoutRepository->find($id);


        if (!$hangout) {
            $this->addFlash('danger', 'Impossible de trouver la sortie.');
            return $this->redirectToRoute('hangout_index');
        }
        if ($hangout->getStatus()->getLabel() === 'Archivee' or $hangout->getStatus()->getLabel() === 'Passée') {
            $this->addFlash('warning', 'Vous ne pouvez pas modifier cette sortie.');
            return $this->redirectToRoute('hangout_index');
        }
        $users = $hangout->getUsers();

        return $this->render('hangout/show.html.twig', ['hangout' => $hangout, 'users' => $users]);
    }

    #[Route('/{id}/list_users', name: 'list_users', methods: ['GET'])]
    public function listUsers(
        int               $id,
        MobileService     $mobileService,
        Request           $request,
        HangoutRepository $hangoutRepository,
    ): Response
    {
        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }
        $hangout = $hangoutRepository->find($id);
        if (!$hangout) {
            $this->addFlash('danger', 'Impossible de trouver la sortie.');
            return $this->redirectToRoute('hangout_index');
        }
        $users = $hangout->getUsers();

        return $this->render('hangout/user-list.html.twig', ['hangout' => $hangout, 'users' => $users]);

    }

    #[Route('/{id}/unsubscribe', name: 'unsubscribe', methods: ['GET', 'POST'])]
    public function unsubscribe(
        int                    $id,
        Request                $request,
        MobileService          $mobileService,
        HangoutRepository      $hangoutRepository,
        EntityManagerInterface $entityManager
    ): Response
    {


        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }
        $hangout = $hangoutRepository->find($id);

        if (!$hangout) {
            $this->addFlash('danger', 'Sortie introuvable.');
            return $this->redirectToRoute('home');
        }

        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecté pour vous désinscrire.');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier que l'utilisateur est inscrit
        if (!$hangout->getUsers()->contains($user)) {
            $this->addFlash('info', 'Vous n\'êtes pas inscrit à cette sortie.');
            return $this->redirectToRoute('hangout_details', ['id' => $id]);
        }

        // Vérifier que la sortie n’est pas passée
        if ($hangout->getStartingDate() < new \DateTime()) {
            $this->addFlash('warning', 'Impossible de se désinscrire d\'une sortie déjà passée.');
            return $this->redirectToRoute('hangout_details', ['id' => $id]);
        }

        // Vérifier que la sortie n’est pas annulée, supprimée, ou en cours
        $status = $hangout->getStatus()->getLabel();
        if (in_array(strtolower($status), ['En cours', 'Annulée', 'Archivée'])) {
            $this->addFlash('warning', 'Vous ne pouvez pas vous désinscrire de cette sortie.');
            return $this->redirectToRoute('hangout_details', ['id' => $id]);
        }


        $hangout->removeUser($user);
        $entityManager->persist($hangout);
        $entityManager->flush();

        $this->addFlash('success', 'Vous avez été désinscrit de la sortie.');

        return $this->redirectToRoute('hangout_details', ['id' => $id]);
    }


    #[Route('/{id}/subscribe', name: 'subscribe', methods: ['GET', 'POST'])]
    public function subscribe(
        int                    $id,
        Request                $request,
        MobileService          $mobileService,
        HangoutRepository      $hangoutRepository,
        EntityManagerInterface $entityManager,
    ): Response
    {

        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }

        $hangout = $hangoutRepository->find($id);
        if (!$hangout) {
            $this->addFlash('danger', 'Sortie introuvable.');
            return $this->redirectToRoute('hangout_index');
        }
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecté(e) pour vous inscrire.');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier que l'utilisateur n'est pas déjà inscrit
        if ($hangout->getUsers()->contains($user)) {
            $this->addFlash('info', 'Vous êtes déjà inscrit à cette sortie.');
            return $this->redirectToRoute('hangout_details', ['id' => $id]);
        }
        // Vérifier que la date limite d'inscription n'est pas dépassée
        if ($hangout->getRegistrationDeadline() < new \DateTime()) {
            $this->addFlash('warning', 'La date limite d\'inscription est dépassée.');
            return $this->redirectToRoute('hangout_details', ['id' => $id]);
        }

        // Vérifier que la sortie n’est pas en status créé
        $status = $hangout->getStatus()->getLabel();
        if (in_array(strtolower($status), ['Créée', 'Annulée', 'Supprimée', 'Archivée'])) {
            $this->addFlash('warning', 'Vous ne pouvez pas vous inscrire de cette sortie.');
            return $this->redirectToRoute('hangout_details', ['id' => $id]);
        }


        // Vérifier qu'il reste de la place
        if (count($hangout->getUsers()) >= $hangout->getNbInscriptionsMax()) {
            $this->addFlash('warning', 'La sortie est complète.');
            return $this->redirectToRoute('hangout_details', ['id' => $id]);
        }

        $hangout->addUser($user);
        $entityManager->persist($hangout);
        $entityManager->flush();

        $this->addFlash('success', 'Vous êtes inscrit à la sortie !');

        return $this->redirectToRoute('hangout_details', ['id' => $id]);


    }

    #[Route('/{id}/delete', name: 'delete', methods: ['GET', 'POST'])]
    public function delete(
        int                    $id,
        Request                $request,
        MobileService          $mobileService,
        EntityManagerInterface $entityManager,
        HangoutRepository      $hangoutRepository,
        StatusRepository       $statusRepository
    ): Response
    {

        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }

        $hangout = $hangoutRepository->find($id);

        if (!$hangout) {
            $this->addFlash('danger', 'Sortie introuvable.');
            return $this->redirectToRoute('hangout_index');
        }

        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('warning', 'Vous devez être connecté pour annuler une sortie.');
            return $this->redirectToRoute('app_login');
        }

        if ($hangout->getOrganizer() !== $user) {
            $this->addFlash('danger', 'Vous n\'êtes pas autorisé à annuler cette sortie.');
            return $this->redirectToRoute('hangout_details', ['id' => $id]);
        }

        // Récupérer le statut "Annulée"
        $statusAnnulee = $statusRepository->findOneBy(['label' => 'Annulée']);

        if (!$statusAnnulee) {
            $this->addFlash('danger', 'Le statut "Annulée" est introuvable.');
            return $this->redirectToRoute('hangout_index');
        }

        // Changer le statut
        $hangout->setStatus($statusAnnulee);
        $entityManager->flush();

        $this->addFlash('success', 'Sortie annulée avec succès.');
        return $this->redirectToRoute('hangout_index');
    }


    #[Route('/{id}/update', name: 'update', methods: ['GET', 'POST'])]
    public function edit(
        int                    $id,
        HangoutRepository      $hangoutRepository,
        StatusRepository       $statusRepository,
        CityRepository         $cityRepository,
        EntityManagerInterface $entityManager,
        Request                $request,
        MobileService          $mobileService,
    ): Response
    {
        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }
        $hangout = $hangoutRepository->find($id);// récuperation de l'entité en bdd
        if (!$hangout) {
            throw $this->createNotFoundException("Le sortie n'existe pas");
        }
        $spots = $entityManager->getRepository(Spot::class)->findAll();
        $spot = $hangout->getSpot();

        $oldLat = $spot->getLatitude();
        $oldLong = $spot->getLongitude();

        $city = $cityRepository->find($spot->getCity()->getId());
        $spot->setCity($city);
        $isOrganizer = $this->getUser() === $hangout->getOrganizer();
        if (!$isOrganizer) {
            throw $this->createAccessDeniedException("L'utilisateur n'est pas l'organisateur ");
        }

        // 2. Créer le formulaire avec les données de l'entité
        $form = $this->createForm(HangoutWithSpotUpdateForm::class, [
            'hangout' => $hangout,
            'spot' => $spot,
        ]);

        // 3. Traiter la requête (récupérer les données POST)
        $zip = $city->getZipCode();

        $form->get('spot')->get('zipCode')->setData($zip);
        $form->handleRequest($request);
//        $updateHangoutForm->handleRequest($request);
//        $updateSpotForm->handleRequest($request);
        // 4. Valider et sauvegarder
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            // Vérifier si le spot sélectionné est dans une ville désactivée
            $selectedSpot = $form->get('hangout')->get('spot')->getData();
            if (!$selectedSpot->getCity()->isActive()) {
                $this->addFlash('error', "Impossible de modifier la sortie vers une ville désactivée.");
                return $this->redirectToRoute('hangout_update', ['id' => $id]);
            }

            //test si button publier a ete cliquer
            if ($form->get('publish')->isClicked()) {
                $hangout->setStatus($statusRepository->findOneBy(['label' => 'Ouverte']));
            }

            //Récupérer le spot sélectionné dans le formulaire
            $selectedSpot = $form->get('hangout')->get('spot')->getData();

            // test si le spot à changer
            if ($selectedSpot->getId() !== $spot->getId()) {
                //rectification entity spot ?
                $spot->setLatitude($oldLat);
                $spot->setLongitude($oldLong);
                // Récupérer les valeurs latitude et longitude modifiées dans le formulaire
                $newLatitude = $form->get('spot')->get('latitude')->getData();
                $newLongitude = $form->get('spot')->get('longitude')->getData();

                $selectedSpot->setLatitude($newLatitude);
                $selectedSpot->setLongitude($newLongitude);
                $data['spot'] = $selectedSpot;
            }


            $entityManager->flush();// Doctrine détecte les changements et les sauvegarde

            // Optionnel : message flash pour confirmation
            $this->addFlash('success', 'Sortie mise à jour avec succès !');
            return $this->redirectToRoute('hangout_index');

        }
//

        return $this->render('hangout/update.html.twig', [
            'id' => $id,
            'form' => $form,
            'spotForm' => $spot,
            'spotALL' => $spots
        ]);
    }

}
