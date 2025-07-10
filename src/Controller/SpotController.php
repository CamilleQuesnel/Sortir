<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Spot;
use App\Form\SpotForm;
use App\Services\MobileService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SpotController extends AbstractController
{
    #[Route('/spot/new', name: 'spot_create')]
    public function create(
        Request $request,
        MobileService $mobileService,
        EntityManagerInterface $em
    ): Response
    {
        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }
        $spot = new Spot();
        $form = $this->createForm(SpotForm::class, $spot);
        $form->handleRequest($request);

//        dump($form->get('cityName')->getData());
//        dump($form->get('zipCode')->getData());
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération des champs non mappés
            $zipCode = $form->get('zipCode')->getData();
            $cityName = $form->get('cityName')->getData();

            // Recherche dans le repo de la ville
            $existingCity = $em->getRepository(City::class)->findOneBy([
                'zipCode' => $zipCode,
                'name' => $cityName
            ]);

            if ($existingCity && !$existingCity->isActive()) {
                $this->addFlash('error', 'Impossible de créer un lieu dans une ville désactivée. Contactez l\'administrateur.');
                return $this->redirectToRoute('spot_create');
            }

            if ($existingCity) {
                $spot->setCity($existingCity);

            } else {
                $newCity = new City();
                $newCity->setZipCode($zipCode);
                $newCity->setName($cityName);
                $em->persist($newCity);
                $spot->setCity($newCity);
            }

            $em->persist($spot);
            $em->flush();

            $this->addFlash('success', 'Lieu enregistré avec succès');
            return $this->redirectToRoute('hangout_publish');
        }

        return $this->render('spot/create.html.twig', [
            'form' => $form,
        ]);
    }

}
