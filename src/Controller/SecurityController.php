<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginFormType;
use App\Form\RegistrationFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Form\ResetPasswordFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app.security.login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // Create the login form
        $form = $this->createForm(LoginFormType::class, [
            'email' => $lastUsername,
        ]);

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/logout', name: 'app.security.logout')]
    public function logout(): Response
    {
        // This method will never be executed as it will be intercepted by the logout key on your firewall
        throw new \LogicException('This method should not be reached!');
    }

    #[Route('/register', name: 'app.security.register')]
    public function register(
        Request $request, 
        UserPasswordHasherInterface $userPasswordHasher, 
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // Generate a flash message
            $this->addFlash('success', 'Your account has been created! You can now log in.');

            return $this->redirectToRoute('app.security.login');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/reset-password', name: 'app.security.reset_password_request')]
    public function resetPasswordRequest(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepository->findByEmail($email);

            if ($user) {
                // In a real application, you would generate a reset token and send an email
                // For this example, we'll just show a success message
                $this->addFlash('success', 'If an account exists with this email, a password reset link has been sent.');
            } else {
                // Don't reveal whether a user account was found or not
                $this->addFlash('success', 'If an account exists with this email, a password reset link has been sent.');
            }

            return $this->redirectToRoute('app.security.login');
        }

        return $this->render('security/reset_password_request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    #[Route('/reset-password/reset', name: 'app.security.reset_password')]
    public function resetPassword(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        // In a real application, you would validate the token here
        // For this example, we'll just show the form

        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // In a real application, you would find the user by the token
            // For this example, we'll just show a success message
            $this->addFlash('success', 'Your password has been reset! You can now log in with your new password.');

            return $this->redirectToRoute('app.security.login');
        }

        return $this->render('security/reset_password.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
} 