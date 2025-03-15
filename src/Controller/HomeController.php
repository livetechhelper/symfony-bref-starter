<?php

namespace App\Controller;

use App\Message\TestMessage;
use App\Service\RequestHelperService;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Envelope;
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
    public function testMessage(MessageBusInterface $bus, LoggerInterface $logger, Connection $connection, Request $request): RedirectResponse
    {
        $message = new TestMessage();
        $message->setMessage("Hey there, this is a test message using SQS");
        
        try {
            $logger->info('Attempting to dispatch message', [
                'message' => $message->getMessage(),
                'class' => get_class($message),
                'interfaces' => class_implements($message)
            ]);
            
            // Check if there are any rows in the messenger_messages table before dispatching
            $beforeCount = $connection->fetchOne('SELECT COUNT(*) FROM messenger_messages');
            $logger->info('Messenger messages before dispatch', ['count' => $beforeCount]);
            
            // Dispatch the actual message
            $envelope = $bus->dispatch($message);
            
            // Check if there are any rows in the messenger_messages table after dispatching
            $afterCount = $connection->fetchOne('SELECT COUNT(*) FROM messenger_messages');
            $logger->info('Messenger messages after dispatch', ['count' => $afterCount]);
            
            $logger->info('Message dispatched successfully', [
                'stamps' => array_map(
                    fn($stampArray) => get_class($stampArray[0]),
                    $envelope->all()
                )
            ]);
            
            if ($afterCount > $beforeCount) {
                $insertedMessage = $connection->fetchAssociative('SELECT * FROM messenger_messages ORDER BY id DESC LIMIT 1');
                $logger->info('Last inserted message', [
                    'id' => $insertedMessage['id'],
                    'queue_name' => $insertedMessage['queue_name'],
                    'body_excerpt' => substr($insertedMessage['body'], 0, 100)
                ]);
            } else {
                $logger->error('No message was inserted into the database!');
            }

            $this->addFlash('success', 'Message queued successfully');  

        } catch (\Throwable $e) {
            $logger->error('Failed to dispatch message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->addFlash('error',  'Failed to queue message: ' . $e->getMessage());
        }

        // Redirect to the referer URL if available, otherwise to the homepage
        $referer = $request->headers->get('referer');
        return $referer ? new RedirectResponse($referer) : new RedirectResponse($this->generateUrl('app.home'));
    }
}
