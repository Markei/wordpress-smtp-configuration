<?php

declare(strict_types=1);

namespace Markei\SmtpConfiguration;

use Attribute;

if (defined('ABSPATH') === false) {
    exit;
}

#[Attribute(Attribute::TARGET_PROPERTY)]
class SensitiveProperty
{

}