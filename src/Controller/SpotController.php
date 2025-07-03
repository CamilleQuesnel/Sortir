<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Spot;
use App\Form\CityForm;
use App\Form\SpotForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SpotController extends AbstractController
{
    #[Route('/spot/new', name: 'spot_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $spot = new Spot();
        $form = $this->createForm(SpotForm::class, $spot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération des champs non mappés
            $zipCode = $form->get('zipCode')->getData();
            $cityName = $form->get('cityName')->getData();

            // Recherche dans le repo de la ville
            $existingCity = $em->getRepository(City::class)->findOneBy([
                'zipCode' => $zipCode,
                'name' => $cityName
            ]);

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
            'form' => $form->createView(),
        ]);
    }

}
