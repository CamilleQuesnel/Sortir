<?php

namespace App\Controller;

use App\Services\MobileService;
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
use Symfony\Component\Validator\Constraints\File;

class CsvController extends AbstractController
{
    /**
     * @throws UnavailableStream
     * @throws InvalidArgument
     * @throws SyntaxError
     * @throws Exception
     */
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/upload-csv', name: 'admin_upload_csv')]
    public function upload(
        Request       $request,
        CsvImporter   $csvImporter,
        MobileService $mobileService,
    ): Response
    {
        if ($mobileService->isMobile($request)) {
            return $this->redirectToRoute('hangout_index');
        }

        // Create a simple form for file upload
        $form = $this->createFormBuilder(null, [
            'attr' => ['id' => 'upload-csv-form']
        ])
            ->add('csv_file', FileType::class, [
                'label' => 'Sélectionnez un fichier CSV',
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'text/csv',
                            'text/plain',
                            'application/vnd.ms-excel', // certains .csv Windows
                            'application/csv',
                        ],
                        'mimeTypesMessage' => 'Le fichier doit être un CSV valide (extension .csv)',
                    ])
                ],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Télécharger sur le serveur', 'attr' => ['class' => 'btn-action'],])
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
                    $this->addFlash('error', "erreur durant l'upload" . $e->getMessage());
                    return $this->redirectToRoute('admin_upload_csv');
                }

                // Process CSV
                $filePath = $uploadsDirectory . '/' . $newFilename;
                $data = $csvImporter->import($filePath);

                $this->addFlash('success', 'CSV téléchargé, trouvé ' . count($data) . ' nouveaux utilisateurs.');

                return $this->redirectToRoute('admin_upload_csv');
            }
        }
        return $this->render('csv/csvUpload.html.twig', [
            'form' => $form,
        ]);
    }
}

