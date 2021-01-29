<?php

declare(strict_types=1);

namespace Sendportal\Base\Models;

use Carbon\Carbon;
use Database\Factories\TagFactory;
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
 * @method static TagFactory factory
 */
class Tag extends BaseModel
{
    use HasFactory;

    // NOTE(david): we require this because of namespace issues when resolving factories from models
    // not in the default `App\Models` namespace.
    protected static function newFactory()
    {
        return TagFactory::new();
    }

    /** @var string */
    protected $table = 'sendportal_tags';

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
        return $this->belongsToMany(Campaign::class, 'sendportal_campaign_tag');
    }

    /**
     * Subscribers in this tag.
     */
    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(Subscriber::class, 'sendportal_tag_subscriber')->withTimestamps();
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
