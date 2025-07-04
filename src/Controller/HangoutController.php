<?php

namespace App\Controller;

use App\Entity\Hangout;
use App\Entity\Spot;
use App\Entity\User;
use App\Form\CreateHangoutForm;
use App\Form\HangoutForm;
use App\Repository\CampusRepository;
use App\Repository\HangoutRepository;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use App\Service\HangoutFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/hangout', name: 'hangout_')]
final class HangoutController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(
        Request           $request,
        HangoutRepository $hangoutRepository,
    ): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(HangoutForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
        }
        $hangouts = $hangoutRepository->findByFilters($user, $filters ?? []);

        return $this->render('hangout/index.html.twig', [
            'hangouts' => $hangouts,
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }


    #[Route('/publish', name: 'publish', methods: ['GET', 'POST'])]
    public function publish(
        Request                $request,
        EntityManagerInterface $entityManager,
        StatusRepository       $statusRepository
    ): Response
    {//transmission des spots
        $spots = $entityManager->getRepository(Spot::class)->findAll();


        $hangout = new Hangout();
        $createHangoutForm = $this->createForm(
            CreateHangoutForm::class,
            $hangout,
            ['user' => $this->getUser(),]);

        $createHangoutForm->handleRequest($request);
        if ($createHangoutForm->isSubmitted() && $createHangoutForm->isValid()) {
            $hangout->setOrganizer($this->getUser());

            $hangout->setCampus($this->getUser()->getCampus());

            if ($createHangoutForm->get('publish')->isClicked()) {
                $status = $statusRepository->findOneBy(['label' => 'Opened']);//quand l'organisateur clique sur publier la sortie
            } else {
                $status = $statusRepository->findOneBy(['label' => 'Created']);//quand l'organisateur clique sur enregistrer elle est juste créée
            }

            $hangout->setStatus($status);

            $entityManager->persist($hangout);
            $entityManager->flush();
            return $this->redirectToRoute('hangout_index');
        }

        return $this->render('hangout/publish.html.twig',
            [
                'createHangoutForm' => $createHangoutForm->createView(),
                'spots' => $spots,
            ]);
    }

    #[Route('/{id}', name: 'details', methods: ['GET', 'Post'])]
    public function details(
        int               $id,
        HangoutRepository $hangoutRepository,
    ): Response
    {
        $hangout = $hangoutRepository->find($id);


        if (!$hangout) {
            $this->addFlash('danger', 'Unable to find Hangout entity.');
            return $this->redirectToRoute('hangout_index');
        }
        $users = $hangout->getUsers();

        return $this->render('hangout/show.html.twig', ['hangout' => $hangout, 'users' => $users]);
    }

    #[Route('/{id}/list_users', name: 'list_users', methods: ['GET'])]
    public function listUsers(
        int $id,
        HangoutRepository $hangoutRepository,
    ) :Response
    {
        $hangout = $hangoutRepository->find($id);
        if (!$hangout) {
            $this->addFlash('danger', 'Unable to find Hangout entity.');
            return $this->redirectToRoute('hangout_index');
        }
        $users = $hangout->getUsers();

        return $this->render('hangout/user-list.html.twig', ['hangout' => $hangout, 'users' => $users]);

    }

    #[Route('/{id}/unsubscribe', name: 'unsubscribe', methods: ['GET', 'POST'])]
    public function unsubscribe(
        int $id,
        HangoutRepository $hangoutRepository,
        EntityManagerInterface $entityManager
    ): Response {
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
        if (in_array(strtolower($status), [ 'En cours', 'Annulée', 'Archivée'])) {
            $this->addFlash('warning', 'Vous ne pouvez pas vous désinscrire de cette sortie.');
            return $this->redirectToRoute('hangout_details', ['id' => $id]);
        }


        $hangout->removeUser($user);
        $entityManager->persist($hangout);
        $entityManager->flush();

        $this->addFlash('success', 'Vous avez été désinscrit de la sortie.');

        return $this->redirectToRoute('hangout_details', ['id' => $id]);
    }


    #[Route('/{id}/subscribe', name: 'subscribe', methods: ['GET', 'Post'])]
    public function subscribe(
        int $id,
        HangoutRepository $hangoutRepository,
        EntityManagerInterface $entityManager,
    ): Response
    {

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
        if (in_array(strtolower($status), [ 'Créée', 'Annulée', 'Supprimée', 'Archivée'])) {
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

    #[Route('/{id}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        Request           $request,
        HangoutRepository $hangoutRepository,
        CampusRepository  $campusRepository,
    ): Response
    {
        return $this->render('hangout/edit.html.twig', []);
    }

}
