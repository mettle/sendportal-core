<?php

declare(strict_types=1);

namespace Sendportal\Base\Models;

use Carbon\Carbon;
use Database\Factories\SubscriberFactory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

/**
 * @property int $id
 * @property int $workspace_id
 * @property string $hash
 * @property string $email
 * @property string|null $first_name
 * @property string|null $last_name
 * @property array|null $meta
 * @property Carbon|null $unsubscribed_at
 * @property int|null $unsubscribed_event_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property EloquentCollection $segments
 * @property EloquentCollection $messages
 *
 * @property-read string $full_name
 *
 * @method static SubscriberFactory factory
 */
class Subscriber extends BaseModel
{
    use HasFactory;

    // NOTE(david): we require this because of namespace issues when resolving factories from models
    // not in the default `App\Models` namespace.
    protected static function newFactory()
    {
        return SubscriberFactory::new();
    }

    /** @var string */
    protected $table = 'sendportal_subscribers';

    /** @var string[] */
    protected $fillable = [
        'hash',
        'email',
        'first_name',
        'last_name',
        'meta',
        'unsubscribed_at',
        'unsubscribe_event_id'
    ];

    /** @var string[] */
    protected $casts = [
        'unsubscribed_at' => 'datetime',
    ];

    public function segments(): BelongsToMany
    {
        return $this->belongsToMany(Segment::class, 'sendportal_segment_subscriber')->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)
            ->orderBy('id', 'desc');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(
            function ($model) {
                $model->hash = Uuid::uuid4()->toString();
            }
        );

        static::deleting(
            function (self $subscriber) {
                $subscriber->segments()->detach();
                $subscriber->messages()->delete();
            }
        );
    }
}
