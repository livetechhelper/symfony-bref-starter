<?php

declare(strict_types=1);

namespace App\Message;

/**
 * Interface for messages that should be processed asynchronously.
 * 
 * Messages implementing this interface will be handled by the queue worker
 * in a Lambda-like environment. This means:
 * - Different environment variables than the web environment
 * - Different PHP settings
 * - Different available services
 * 
 * Use this interface for:
 * - Background processing tasks
 * - Operations that can be delayed
 * - Tasks that need AWS Lambda environment variables
 * - Operations that might need retry handling
 */
interface AsyncMessageInterface extends MessageInterface
{
}