<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/queue', name: 'app.queue.')]
class QueueController extends AbstractController
{
    /**
     * Overview page for queue status
     */
    #[Route('', name: 'index')]
    public function index(Connection $connection): Response
    {
        // Get count of messages in queue
        $messagesCount = $connection->fetchOne('SELECT COUNT(*) FROM messenger_messages');
        
        // Get stats by queue name
        $queueStats = [
            'async' => 0,
            'failed' => 0
        ];
        
        $queueData = $connection->fetchAllAssociative('
            SELECT queue_name, COUNT(*) as count, 
                   MIN(created_at) as oldest_message, 
                   MAX(created_at) as newest_message
            FROM messenger_messages
            GROUP BY queue_name
        ');
        
        // Format queue stats data as an associative array with queue names as keys
        foreach ($queueData as $data) {
            $queueName = $data['queue_name'];
            $queueStats[$queueName] = (int)$data['count'];
        }
        
        return $this->render('queue/index.html.twig', [
            'messagesCount' => $messagesCount,
            'queueStats' => $queueStats
        ]);
    }
    
    /**
     * View details of all messages in a specific queue
     */
    #[Route('/list/{queueName}', name: 'list', defaults: ['queueName' => 'async'])]
    public function listMessages(Connection $connection, string $queueName): Response
    {
        // Get all messages for the specified queue
        $messages = $connection->fetchAllAssociative('
            SELECT id, body, headers, queue_name, created_at, available_at, delivered_at
            FROM messenger_messages
            WHERE queue_name = :queue_name
            ORDER BY created_at DESC
        ', ['queue_name' => $queueName]);
        
        // Always include both default queues
        $queues = ['async', 'failed'];
        
        // Get any additional queues from the database
        $dbQueues = $connection->fetchFirstColumn('
            SELECT DISTINCT queue_name FROM messenger_messages ORDER BY queue_name
        ');
        
        // Merge and remove duplicates
        $queues = array_unique(array_merge($queues, $dbQueues));
        sort($queues);
        
        return $this->render('queue/list.html.twig', [
            'messages' => $messages,
            'queueName' => $queueName,
            'queues' => $queues
        ]);
    }
    
    /**
     * View detailed information about a specific message
     */
    #[Route('/message/{id}', name: 'message_detail')]
    public function messageDetail(Connection $connection, int $id): Response
    {
        // Get the message details
        $message = $connection->fetchAssociative('
            SELECT id, body, headers, queue_name, created_at, available_at, delivered_at
            FROM messenger_messages
            WHERE id = :id
        ', ['id' => $id]);
        
        if (!$message) {
            throw $this->createNotFoundException('Message not found');
        }
        
        // Try to decode the message body to display more readable information
        $decodedBody = json_decode($message['body'], true);
        
        // Try to decode headers
        $decodedHeaders = json_decode($message['headers'], true);
        
        return $this->render('queue/message_detail.html.twig', [
            'message' => $message,
            'decodedBody' => $decodedBody,
            'decodedHeaders' => $decodedHeaders
        ]);
    }
} 