<?php

declare(strict_types=1);

namespace Markei\SmtpConfiguration\Exception;

class OAuthException extends \RuntimeException
{
    public function __construct(
        string $message,
        public readonly ?int $httpStatus = null,
        public readonly ?string $remoteError = null,
        public readonly array $data = []
    )
    {
        parent::__construct($message);
    }
}