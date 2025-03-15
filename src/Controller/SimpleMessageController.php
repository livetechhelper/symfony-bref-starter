<?php

namespace App\Controller;

use App\Message\SimpleMessage;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class SimpleMessageController extends AbstractController
{
    #[Route('/simple-message', name: 'app.simple_message')]
    public function testMessage(MessageBusInterface $bus, LoggerInterface $logger, Connection $connection): Response
    {
        $message = new SimpleMessage();
        $message->setMessage("This is a simple test message");
        
        try {
            $logger->info('Attempting to dispatch simple message', [
                'message' => $message->getMessage(),
                'class' => get_class($message)
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
            
            return new JsonResponse([
                'message' => $message->getMessage(),
                'log' => "Simple message successfully queued",
                'message_count_before' => $beforeCount,
                'message_count_after' => $afterCount
            ]);
        } catch (\Throwable $e) {
            $logger->error('Failed to dispatch message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return new JsonResponse([
                'error' => 'Failed to queue message: ' . $e->getMessage()
            ], 500);
        }
    }
} 