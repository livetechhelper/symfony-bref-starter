<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/dashboard')]
#[IsGranted('ROLE_USER')]
class DashboardController extends AbstractController
{
    #[Route('', name: 'app.dashboard')]
    public function index(): Response
    {
        // Get the current user
        $user = $this->getUser();
        
        return $this->render('dashboard/index.html.twig', [
            'user' => $user,
        ]);
    }
} 