<?php

declare(strict_types=1);

namespace Sendportal\Base\Models;


use  Carbon\Carbon;
use Database\Factories\SegmentFactory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $workspace_id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property EloquentCollection $campaigns
 * @property EloquentCollection $subscribers
 * @property EloquentCollection $active_subscribers
 *
 * @method static SegmentFactory factory
 */
class Segment extends BaseModel
{
    use HasFactory;

    protected $table = 'sendportal_segments';

    protected $guarded = [];

    /** @var array */
    protected $withCount = [
        'subscribers'
    ];

    protected static function newFactory()
    {
        return SegmentFactory::new();
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'sendportal_campaign_segments');
    }

    /**
     * Subscribers in this tag.
     */
    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(Subscriber::class, 'assets', 'contract', 'user_id')->as('asset')->where('subscribers.workspace_id', $this->workspace_id)
            ->withPivot('user_id', 'sc_user_id')->withTimestamps();
    }

    /**
     * Active subscribers in this tag.
     */
    public function activeSubscribers(): BelongsToMany
    {
        return $this->subscribers()
            ->whereNull('unsubscribed_at')
            ->withTimestamps();
    }
}
