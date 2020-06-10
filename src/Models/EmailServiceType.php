<?php

declare(strict_types=1);

namespace Sendportal\Base\Models;

class EmailServiceType extends BaseModel
{
    public const SES      = 1;
    public const SENDGRID = 2;
    public const MAILGUN  = 3;
    public const POSTMARK = 4;
    public const ELASTIC  = 5;

    /** @var array */
    protected static $types = [
        self::SES      => 'SES',
        self::SENDGRID => 'Sendgrid',
        self::MAILGUN  => 'Mailgun',
        self::POSTMARK => 'Postmark',
        self::ELASTIC  => 'ElasticEmail',
    ];

    /**
     * Resolve a type ID to a type name.
     */
    public static function resolve(int $typeId): ?string
    {
        return static::$types[$typeId] ?? null;
    }
}
