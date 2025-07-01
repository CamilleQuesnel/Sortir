<?php

namespace App\Controller;

use App\Entity\Hangout;
use App\Form\HangoutForm;
use App\Repository\CampusRepository;
use App\Repository\HangoutRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/hangout', name: 'hangout_')]
final class HangoutController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        Request           $request,
        HangoutRepository $hangoutRepository,
        CampusRepository  $campusRepository,
    ): Response
    {
        $hangout = new Hangout();
        $form = $this->createForm(HangoutForm::class, $hangout);
        $form->handleRequest($request);

        $hangoutAll = $hangoutRepository->findAll();
        $campusALL = $campusRepository->findAll();

        return $this->render('hangout/index.html.twig', [
            'controller_name' => 'HangoutController',
            'form' => $form,
            'hangoutAll' => $hangoutAll,
            'campusAll' => $campusALL,

        ]);
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
    #[Route('/{id}', name: 'publish', methods: ['GET','Post'])]
    public function publish(
        Request           $request,
        HangoutRepository $hangoutRepository,
        CampusRepository  $campusRepository,
    ): Response
    {
        return $this->render('hangout/publish.html.twig', []);
    }
    #[Route('/{id}', name: 'show', methods: ['GET','Post'])]
    public function show(
        Request           $request,
        HangoutRepository $hangoutRepository,
        CampusRepository  $campusRepository,
    ): Response
    {
        return $this->render('hangout/show.html.twig', []);
    }
    #[Route('/{id}', name: 'unsubscribe', methods: ['GET','Post'])]
    public function unsubscribe(
        Request           $request,
        HangoutRepository $hangoutRepository,
        CampusRepository  $campusRepository,
    ): Response
    {
        return $this->render('hangout/unsubscribe.html.twig', []);
    }
    #[Route('/{id}', name: 'subscribe', methods: ['GET','Post'])]
    public function subscribe(
        Request           $request,
        HangoutRepository $hangoutRepository,
        CampusRepository  $campusRepository,
    ): Response
    {
        return $this->render('hangout/subscribe.html.twig', []);
    }


}
