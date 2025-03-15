<?php

declare(strict_types=1);

namespace App\Message;

/**
 * Interface for messages that should be processed synchronously.
 * 
 * Messages implementing this interface will be handled immediately in the web environment.
 * Be aware that this environment is different from the Lambda environment:
 * - Different environment variables available
 * - Different PHP settings
 * - Different service availability
 * 
 * Use this interface only for:
 * - Operations that must complete before the response is sent
 * - Operations that affect the user's immediate experience
 * - Operations that don't depend on Lambda-specific environment variables
 * 
 * Warning: If you later change a sync message to async, be sure to test thoroughly
 * as the processing environment will be different.
 */
interface SyncMessageInterface extends MessageInterface
{
} 