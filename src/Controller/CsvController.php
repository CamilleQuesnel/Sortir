<?php

namespace App\Controller;

use League\Csv\Exception;
use League\Csv\InvalidArgument;
use League\Csv\SyntaxError;
use League\Csv\UnavailableStream;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Services\CsvImporter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CsvController extends AbstractController
{
    /**
     * @throws UnavailableStream
     * @throws InvalidArgument
     * @throws SyntaxError
     * @throws Exception
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/upload-csv', name: 'upload_csv')]
    public function upload(Request $request, CsvImporter $csvImporter): Response
    {
        // Create a simple form for file upload
        $form = $this->createFormBuilder()
            ->add('csv_file', FileType::class, ['label' => 'Sélectionnez un fichier csv'])
            ->add('submit', SubmitType::class, ['label' => 'Télécharger sur le serveur'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $csvFile */
            $csvFile = $form->get('csv_file')->getData();

            if ($csvFile) {
                $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/upload/csv';
                $newFilename = uniqid() . '.' . $csvFile->guessExtension();

                try {
                    $csvFile->move($uploadsDirectory, $newFilename);
                } catch (FileException $e) {
                    return new Response('Error uploading file: ' . $e->getMessage());
                }

                // Process CSV
                $filePath = $uploadsDirectory . '/' . $newFilename;
                $data = $csvImporter->import($filePath);

                return new Response('CSV uploaded and processed! Found ' . count($data) . ' records.');
            }
        }

        return $this->render('csv/csvUpload.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

