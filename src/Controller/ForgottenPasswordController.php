<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ForgottenPasswordFormType;
use App\Form\ResetPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Uid\Uuid;

final class ForgottenPasswordController extends AbstractController
{
    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/forgotten/password', name: 'app_forgotten_password')]
    public function index(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ForgottenPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $email = $form->get('mail')->getData();

            $user = $em->getRepository(User::class)->findOneBy(['mail' => $email]);

            if (!$user) {
                $form->get('mail')->addError(new FormError("Aucun compte avec cet e-mail."));
            } else {
                $token = Uuid::v4()->toRfc4122();
                $user->setResetToken($token);
                $user->setResetRequestedAt(new \DateTime());

                $em->flush();

                $resetUrl = $this->generateUrl('reset_password', ['token' => $token], true);

                $email = (new TemplatedEmail())
                    ->from(new Address('no-reply@tonsite.fr', 'Support'))
                    ->to($user->getMail())
                    ->subject('Réinitialisation de votre mot de passe')
                    ->htmlTemplate('emails/reset_password.html.twig')
                    ->context(['resetUrl' => $resetUrl]);

                try {
                    $mailer->send($email);
                } catch (\Exception $e) {
                    dump($e->getMessage());
                }

                $this->addFlash('success', 'Un e-mail de réinitialisation a été envoyé.');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('forgotten_password/index.html.twig', [
            'form' => $form,
        ]);
    }
    #[Route('/reset-password/{token}', name: 'reset_password')]
    public function resetPassword(
        string $token,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em
    ): Response {
        $user = $em->getRepository(User::class)->findOneBy(['resetToken' => $token]);

        if (!$user || $user->getResetRequestedAt() < (new \DateTime())->modify('-1 hour')) {
            $this->addFlash('danger', 'Lien invalide ou expiré.');
            return $this->redirectToRoute('app_forgotten_password');
        }

        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );
            $user->setPassword($hashedPassword);
            $user->setResetToken(null);
            $user->setResetRequestedAt(null);

            $em->flush();

            $this->addFlash('success', 'Mot de passe mis à jour avec succès.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('forgotten_password/reset_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
