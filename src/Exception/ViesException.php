<?php

namespace Wexample\SymfonyRemoteVies\Exception;

use RuntimeException;

/**
 * VIES gave no verdict: unreachable, overloaded, or the member state's own register
 * away (MS_UNAVAILABLE, TIMEOUT…), which happens daily. The number is neither valid
 * nor invalid, only unchecked: worth a retry later, never read as a refusal.
 */
class ViesException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?string $errorCode = null,
    ) {
        parent::__construct($message);
    }
}
