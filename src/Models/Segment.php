<?php

declare(strict_types=1);

namespace Sendportal\Base\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Segment extends BaseModel
{
    protected $table = 'sendportal_segments';

    /** @var array */
    protected $fillable = [
        'name',
    ];

    /** @var array */
    protected $withCount = [
        'subscribers'
    ];

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'sendportal_campaign_segment');
    }

    /**
     * Subscribers in this segment.
     */
    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(Subscriber::class, 'sendportal_segment_subscriber')->withTimestamps();
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
