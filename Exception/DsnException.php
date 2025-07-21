<?php

declare(strict_types=1);

namespace Markei\SmtpConfiguration\Exception;

class DsnException extends \InvalidArgumentException
{
    public function __construct(
        string $message,
        public readonly array $data = []
    )
    {
        parent::__construct($message);
    }
}