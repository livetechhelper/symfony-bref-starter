<?php

namespace App\MessageHandler;

use App\Message\SimpleMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SimpleMessageHandler
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(SimpleMessage $message): void
    {
        $this->logger->info("Simple message received with message: [{$message->getMessage()}]");
    }
} 