<?php

namespace App\Controller;

use App\Entity\Hangout;
use App\Entity\Spot;
use App\Form\CreateHangoutForm;
use App\Form\HangoutForm;
use App\Repository\CampusRepository;
use App\Repository\HangoutRepository;
use App\Repository\StatusRepository;
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
                $status = $statusRepository->findOneBy(['label' => 'Opened']);
            } else {
                $status = $statusRepository->findOneBy(['label' => 'Closed']);
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

        return $this->render('hangout/show.html.twig', ['hangout' => $hangout]);
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
