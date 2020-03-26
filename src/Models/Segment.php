<?php

declare(strict_types=1);

namespace Sendportal\Base\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Segment extends BaseModel
{
    /** @var array */
    protected $fillable = [
        'name',
    ];

    /** @var array */
    protected $withCount = [
        'subscribers'
    ];

    /**
     * The workspace this segment belongs to.
     */
    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    /**
     * Subscribers in this segment.
     */
    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(Subscriber::class)->withTimestamps();
    }

    /**
     * Active subscribers in this segment.
     */
    public function activeSubscribers(): BelongsToMany
    {
        return $this->belongsToMany(Subscriber::class)
            ->whereNull('unsubscribed_at')
            ->withTimestamps();
    }
}
