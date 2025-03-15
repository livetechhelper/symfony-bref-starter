<?php


namespace App\MessageHandler;

use App\Message\TestMessage;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TestMessageHandler
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

    public function __invoke(TestMessage $message): void
    {
        $this->logger->info("Test message received with message: [{$message->getMessage()}]");

        // Write debug info to file
        $debugDir = __DIR__ . '/../../var/data';
        if (!is_dir($debugDir)) {
            mkdir($debugDir, 0777, true);
        }
        
        $debugFile = $debugDir . '/shoes.txt';
        $timestamp = (new \DateTime())->format('Y-m-d H:i:s');
        $debugMessage = sprintf(
            "[%s] Message received: %s\n",
            $timestamp,
            $message->getMessage()
        );
        
        file_put_contents($debugFile, $debugMessage, FILE_APPEND);

        // throw new \Exception('Test exception');
    }
}