<?php

namespace App\Controller;

use App\Message\TestMessage;
use App\Repository\WidgetRepository;
use App\Service\RequestHelperService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app.home')]
    public function index(RequestHelperService $requestHelperService): Response
    {
        // use the request helper so we can get the request details (will be empty for local)
        $data = [
            'user_ip' => $requestHelperService->getIpAddressFromCurrentRequest(),
            'user_country' => $requestHelperService->getCountryFromCurrentRequest(),
            'user_city' => $requestHelperService->getCityFromCurrentRequest(),
            'user_region' => $requestHelperService->getRegionFromCurrentRequest(),
        ];

        return $this->render('home/index.html.twig', [
            'data' => $data
        ]);
    }

    #[Route('/test-message', name: 'app.test_message')]
    public function testMessage(MessageBusInterface $bus): Response
    {
        $message = new TestMessage();
        $message->setMessage("Hey there, this is a test message using SQS");
        $bus->dispatch($message);

        return new JsonResponse([
            'message' => $message->getMessage(),
            'log' => "Message successfully queued, look in your logs for the message being processed"
        ]);
    }

    #[Route('/test-db', name: 'app.test_db')]
    public function testDB(WidgetRepository $widgetRepository): Response
    {
        $widgets = [];
        $error = null;

        try {
            $widgets = $widgetRepository->findAll();
        } catch (\Exception $e) {
            $error = $e;
        }

        return $this->render('home/test_db.html.twig', [
            'widgets' => $widgets,
            'error' => $error
        ]);
    }

    #[Route('/test-session', name: 'app.test_session')]
    public function testSession(Request $request): Response
    {
        $session = null;
        $error = null;

        try {
            $session = $request->getSession();
        } catch (\Exception $e) {
            $error = $e;
        }

        return $this->render('home/test_session.html.twig', [
            'session' => $session,
            'error' => $error
        ]);
    }
}
