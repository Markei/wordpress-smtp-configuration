<?php

declare(strict_types=1);

namespace Markei\SmtpConfiguration;

use InvalidArgumentException;
use Markei\SmtpConfiguration\Exception\DsnException;
use ReflectionObject;
use ReflectionProperty;

class Dsn
{
    const PROTOCOLS = [self::PROTOCOL_MAIL, self::PROTOCOL_SMTP, self::PROTOCOL_SENDMAIL];
    const PROTOCOL_MAIL = 'mail';
    const PROTOCOL_SMTP = 'smtp';
    const PROTOCOL_SENDMAIL = 'sendmail';

    const AUTH_METHODS = [self::AUTH_METHOD_NONE, self::AUTH_METHOD_BASIC, self::AUTH_METHOD_XOAUTH2];
    const AUTH_METHOD_NONE = 'none';
    const AUTH_METHOD_BASIC = 'basic';
    const AUTH_METHOD_XOAUTH2 = 'xoauth2';

    const ENCRYPTION_METHODS = [self::ENCRYPTION_NONE, self::ENCRYPTION_SSL, self::ENCRYPTION_STARTTLS];
    const ENCRYPTION_NONE = 'none';
    const ENCRYPTION_SSL = 'ssl';
    const ENCRYPTION_STARTTLS = 'starttls';

    public readonly string $protocol;
    public readonly string $host;
    public readonly int $port;
    public readonly string $encryption;
    public readonly string $auth;
    public readonly string $username;
    #[SensitiveProperty] public readonly string $password;
    public readonly ?string $clientId;
    #[SensitiveProperty] public readonly ?string $clientSecret;
    public readonly ?string $tokenEndpoint;
    public readonly ?string $authEndpoint;
    public readonly ?array $authEndpointParameters;
    public readonly ?string $scope;
    public readonly ?bool $useCache;
    public readonly ?bool $debug;

    public function __construct(string $dsn)
    {
        $settings = [];
        preg_match('/(?<protocol>smtp|sendmail|mail*):\/\/(?<host>[\w\.]*)(:(?<port>\d*))?(\?(?<options>.*))?$/', $dsn, $settings);
        if (isset($settings['options']) === false) {
            $settings['options'] = '';
        }

        $options = [];
        parse_str($settings['options'], $options);
        $settings['options'] = $options;

        if (in_array($settings['protocol'], self::PROTOCOLS) === false) {
            throw new DsnException('Protocol not supported', ['givenProtocol' => $settings['protocol']]);
        }
        $this->protocol = $settings['protocol'];

        $this->debug = isset($settings['options']['debug']) === true ? boolval($settings['options']['debug']) : false;

        if ($this->protocol === self::PROTOCOL_SMTP) {
            if (empty($settings['host']) === true) {
                $settings['host'] = 'localhost';
            }
            $this->host = $settings['host'];

            $settings['port'] = intval(isset($settings['port']) ? $settings['port'] : '');
            if ($settings['port'] === 0) {
                $settings['port'] = 25;
            }
            $this->port = $settings['port'];

            if (isset($settings['options']['encryption']) === false) {
                $settings['options']['encryption'] = self::ENCRYPTION_NONE;
            }
            if ($settings['options']['encryption'] === 'tls') {
                $settings['options']['encryption'] = 'starttls';
            }
            if (in_array($settings['options']['encryption'], self::ENCRYPTION_METHODS) === false) {
                throw new DsnException('Encryption method not supported', ['givenEncryption' => $settings['options']['encryption']]);
            }
            $this->encryption = $settings['options']['encryption'];

            if ($this->protocol === self::PROTOCOL_SMTP && isset($settings['options']['auth']) === false && isset($settings['options']['username']) === false) {
                $settings['options']['auth'] = self::AUTH_METHOD_NONE;
            }
            if ($this->protocol === self::PROTOCOL_SMTP && isset($settings['options']['username']) === true && isset($settings['options']['auth']) === false) {
                $settings['options']['auth'] = self::AUTH_METHOD_BASIC;
            }
            if (in_array($settings['options']['auth'], self::AUTH_METHODS) === false) {
                throw new DsnException('Auth method not supported', ['givenAuthMethod' => $settings['options']['auth']]);
            }
            $this->auth = $settings['options']['auth'];

            $this->username = isset($settings['options']['username']) ? $settings['options']['username'] : '';

            $this->password = isset($settings['options']['password']) ? $settings['options']['password'] : '';

            $this->clientId = isset($settings['options']['client_id']) ? $settings['options']['client_id'] : null;

            $this->clientSecret = isset($settings['options']['client_secret']) ? $settings['options']['client_secret'] : null;

            $this->tokenEndpoint = isset($settings['options']['token_endpoint']) ? $settings['options']['token_endpoint'] : null;

            $this->authEndpoint = isset($settings['options']['auth_endpoint']) ? $settings['options']['auth_endpoint'] : null;

            if ($this->authEndpoint !== null && isset($settings['options']['auth_endpoint_parameters'])) {
                if (is_array($settings['options']['auth_endpoint_parameters']) === false) {
                    $settings['options']['auth_endpoint_parameters'] = [$settings['options']['auth_endpoint_parameters']];
                }
                $this->authEndpointParameters = $settings['options']['auth_endpoint_parameters'];
            }

            $this->scope = isset($settings['options']['scope']) ? $settings['options']['scope'] : null;

            if ($this->protocol === self::PROTOCOL_SMTP && $settings['options']['auth'] === self::AUTH_METHOD_XOAUTH2 && isset($settings['options']['cache']) === false) {
                $settings['options']['cache'] = false;
            }
        }

        $settings['options']['cache'] = isset($settings['options']['cache']) ? boolval($settings['options']['cache']) : false;
        $this->useCache = $settings['options']['cache'];
    }

    public function toArray(): array
    {
        $output = [];

        $reflection = new ReflectionObject($this);
        foreach ($reflection->getProperties() as $property) {
            assert($property instanceof ReflectionProperty);
            if ($property->isInitialized($this) && ($value = $property->getValue($this)) !== null) {
                $output[$property->getName()] = [
                    'type' => $property->getType()->getName(),
                    'sensitive' => count($property->getAttributes(SensitiveProperty::class)) > 0,
                    'value' => $property->getValue($this)
                ];
            }
        }

        return $output;
    }

}