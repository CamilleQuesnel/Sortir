<?php

namespace App\Controller;

use App\DTO\HangoutFilterDTO;
use App\Entity\Hangout;
use App\Form\CreateHangoutForm;
use App\Form\HangoutForm;
use App\Repository\CampusRepository;
use App\Repository\HangoutRepository;
use App\Service\HangoutFilterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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


        $filters = $form->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
        }
        dump($user);

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
        EntityManagerInterface $entityManager
    ): Response
    {
        $hangout = new Hangout();
        $createHangoutForm = $this->createForm(CreateHangoutForm::class, $hangout, ['user' => $this->getUser(),]);

        $createHangoutForm->handleRequest($request);
        if ($createHangoutForm->isSubmitted() && $createHangoutForm->isValid()) {
            $hangout->setOrganizer($this->getUser());

            $entityManager->persist($hangout);
            $entityManager->flush();
            return $this->redirectToRoute('hangout_index');
        }

        return $this->render('hangout/publish.html.twig', ['createHangoutForm' => $createHangoutForm->createView()]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET', 'Post'])]
    public function show(
        Request           $request,
        HangoutRepository $hangoutRepository,
        CampusRepository  $campusRepository,
    ): Response
    {
        return $this->render('hangout/show.html.twig', []);
    }

    #[Route('/{id}', name: 'unsubscribe', methods: ['GET', 'Post'])]
    public function unsubscribe(
        Request           $request,
        HangoutRepository $hangoutRepository,
        CampusRepository  $campusRepository,
    ): Response
    {
        return $this->render('hangout/unsubscribe.html.twig', []);
    }

    #[Route('/{id}', name: 'subscribe', methods: ['GET', 'Post'])]
    public function subscribe(
        Request           $request,
        HangoutRepository $hangoutRepository,
        CampusRepository  $campusRepository,
    ): Response
    {
        return $this->render('hangout/subscribe.html.twig', []);
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
