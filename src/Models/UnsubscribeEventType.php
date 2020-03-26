<?php

namespace Sendportal\Base\Models;

class UnsubscribeEventType extends BaseModel
{
    const BOUNCE = 1;
    const COMPLAINT = 2;
    const MANUAL_BY_ADMIN = 3;
    const MANUAL_BY_SUBSCRIBER = 4;

    public static $types = [
        1 => 'Bounced',
        2 => 'Complained',
        3 => 'Manual by Admin',
        4 => 'Manual by Subscriber',
    ];

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the unsubscribe type by ID
     *
     * @param int $id
     * @return mixed
     */
    public static function findById($id): string
    {
        return \Arr::get(static::$types, $id);
    }
}
