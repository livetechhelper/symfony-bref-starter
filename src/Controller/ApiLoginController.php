<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApiLoginController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(): Response
    {
        // This controller method is not used directly
        // The JSON login authenticator from security.yaml intercepts the request
        // and the LexikJWTAuthenticationBundle takes care of generating the token
        
        // This route exists only to have a proper endpoint for the Swagger UI
        throw new \LogicException('This method should not be reached!');
    }
} 