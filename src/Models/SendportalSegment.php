<?php

declare(strict_types=1);

namespace Sendportal\Base\Models;


use Database\Factories\SegmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
class SendportalSegment extends Model
{
    use HasFactory;

    protected $table = 'sendportal_segments';

    protected $guarded = [];

    protected static function newFactory()
    {
        return SegmentFactory::new();
    }

    public function campaigns(): BelongsToMany
    {
        return $this->belongsToMany(Campaign::class, 'sendportal_campaign_segments');
    }
}
