<?php

namespace App\Controller;

use App\Entity\Hangout;
use App\Entity\Spot;
use App\Form\CreateHangoutForm;
use App\Form\HangoutForm;
use App\Form\SpotForm;
use App\Repository\CampusRepository;
use App\Repository\CityRepository;
use App\Repository\HangoutRepository;
use App\Repository\SpotRepository;
use App\Repository\StatusRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Metadata\Exception;
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
        StatusRepository $statusRepository,
    ): Response
    {
        $user = $this->getUser();

        $hangouts = $hangoutRepository->findByFilters($user, $filters ?? []);
        //Verification date des sorties
        $today = new DateTimeImmutable('today');
        $nextMonth = $today->modify('+1 month');

        foreach ($hangouts as $sortie) {
            if ($sortie->getStartingDate() < $today && $sortie->getStatus()->getLabel() !== 'Passée') {
                $statusPassee = $statusRepository->findOneBy(['label' => 'Passée']);
                $sortie->setStatus($statusPassee);
            }
            if ($sortie->getStartingDate() < $nextMonth && $sortie->getStatus()->getLabel() === 'Passée') {
                $statusArchivee = $statusRepository->findOneBy(['label' => 'Archivée']);
                $sortie->setStatus($statusArchivee);
            }
        }

        $form = $this->createForm(HangoutForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
        }

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
        if ($hangout->getStatus()->getLabel() === 'Archivee' or $hangout->getStatus()->getLabel() === 'Passée') {
            $this->addFlash('warning', 'Vous ne pouvez pas modifier cette sortie.');
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

    #[Route('/{id}/update', name: 'update', methods: ['GET', 'POST'])]
    public function edit(
        int $id,
        HangoutRepository $hangoutRepository,
        SpotRepository $spotRepository,
        CityRepository  $cityRepository,
        EntityManagerInterface $entityManager,
        Request           $request,
    ): Response
    {


        $hangout = $hangoutRepository->find($id);
        $spot   = $spotRepository->find($hangout->getSpot()->getId());
        $city =  $cityRepository->find($spot->getCity()->getId());

        $formData = [
            'hangout' => $hangout,
            'spot' => $spot,
        ];

        if (!$hangout) {
            throw $this->createNotFoundException("Le sortie n'existe pas");
        }
//        dd($this->getUser());
        $isOrganizer = $this->getUser()->getUserIdentifier() === $hangout->getOrganizer()->getUserIdentifier();

            if (!$isOrganizer){
                throw $this->createAccessDeniedException("L'utilisateur n'est pas l'organisateur ");
            }

        $updateHangoutForm = $this->createForm(CreateHangoutForm::class, $hangout,['user' => $this->getUser()]);
        $updateHangoutForm->handleRequest($request);
        $updateSpotForm = $this->createForm(SpotForm::class, $spot);
        $updateSpotForm->get('zipCode')->setData($spot->getCity()->getZipCode());
        $updateSpotForm->get('cityName')->setData($spot->getCity()->getName());
        $updateSpotForm->handleRequest($request);

        if (($updateHangoutForm->isSubmitted() && $updateHangoutForm->isValid()) &&($updateSpotForm->isSubmitted() && $updateSpotForm->isValid()) ) {
            $hangout->setOrganizer($this->getUser());

            try {
                $entityManager->persist($hangout);
                $entityManager->flush();
                $this->addFlash('success', "Sortie Modifiée");
                return $this->redirectToRoute('hangout_index');
            }catch(Exception $e){
                $this->addFlash('warning', $e->getMessage());
            }
        }
        return $this->render('hangout/update.html.twig', [
            'createHangoutForm' => $updateHangoutForm->createView(),
            'SpotForm'=>$updateSpotForm->createView(),
        ]);
    }

}
