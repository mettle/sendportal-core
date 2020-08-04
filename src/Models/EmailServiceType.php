<?php

declare(strict_types=1);

namespace Sendportal\Base\Models;

class EmailServiceType extends BaseModel
{
    public const SES = 1;
    public const SENDGRID = 2;
    public const MAILGUN = 3;
    public const POSTMARK = 4;
    public const MAILJET = 5;

    /** @var array */
    protected static $types = [
        self::SES => 'SES',
        self::SENDGRID => 'Sendgrid',
        self::MAILGUN => 'Mailgun',
        self::POSTMARK => 'Postmark',
        self::MAILJET => 'Mailjet',
    ];

    /**
     * Resolve a type ID to a type name.
     */
    public static function resolve(int $typeId): ?string
    {
        return static::$types[$typeId] ?? null;
    }

    public static function resolveValidationRules($typeId): array
    {
        switch ($typeId) {
            case self::SES:
                return [
                    'settings.key' => 'required',
                    'settings.secret' => 'required',
                    'settings.region' => 'required',
                    'settings.configuration_set_name' => 'required'
                ];

            case self::SENDGRID:
            case self::POSTMARK:
                return [
                    'settings.key' => 'required',
                ];

            case self::MAILGUN:
                return [
                    'settings.key' => 'required',
                    'settings.domain' => 'required',
                    'settings.zone' => ['required', 'in:US,EU']
                ];

            case self::MAILJET:
                return [
                    'settings.key' => 'required',
                    'settings.secret' => 'required',
                    'settings.zone' => ['required', 'in:Default,US'],
                ];

            default:
                return [];
        }
    }

    public static function resolveValidationMessages($typeId): array
    {
        switch ($typeId) {
            case self::SES:
                return [
                    'settings.key.required' => __('The AWS Email Service requires you to enter a key'),
                    'settings.secret.required' => __('The AWS Email Service requires you to enter a secret'),
                    'settings.region.required' => __('The AWS Email Service requires you to enter a region'),
                    'settings.configuration_set_name.required' => __('The AWS Email Service requires you to enter a configuration set name'),
                ];

            case self::SENDGRID:
                return [
                    'settings.key.required' => __('The Sendgrid Email Service requires you to enter a key'),
                ];

            case self::POSTMARK:
                return [
                    'settings.key.required' => __('The Postmark Email Service requires you to enter a key'),
                ];

            case self::MAILGUN:
                return [
                    'settings.key.required ' =>  __('The Mailgun Email Service requires you to enter a key'),
                    'settings.domain.required' => __('The Mailgun Email Service requires you to enter a domain'),
                    'settings.zone.required' => __('The Mailgun Email Service requires you to enter a zone'),
                ];

            case self::MAILJET:
                return [
                    'settings.key.required' =>  __('The Mailjet Email Service requires you to enter a key'),
                    'settings.secret.required' =>  __('The Mailgun Email Service requires you to enter a secret'),
                    'settings.zone.required' =>  __('The Mailgun Email Service requires you to enter a zone'),
                ];

            default:
                return [];
        }
    }
}
