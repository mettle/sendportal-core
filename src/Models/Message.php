<?php

declare(strict_types=1);

namespace Sendportal\Base\Models;

use Carbon\Carbon;
use Database\Factories\MessageFactory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Ramsey\Uuid\Uuid;
use Sendportal\Base\Facades\Helper;
use Sendportal\Pro\Models\AutomationSchedule;

/**
 * @property int $id
 * @property string $hash
 * @property int $workspace_id
 * @property int $subscriber_id
 * @property string $source_type
 * @property int $source_id
 * @property string $recipient_email
 * @property string $subject
 * @property string $from_name
 * @property string $from_email
 * @property string $reply_to
 * @property ?string $message_id
 * @property ?string $ip
 * @property int $open_count
 * @property int $click_count
 * @property Carbon|null $queued_at
 * @property Carbon|null $sent_at
 * @property Carbon|null $delivered_at
 * @property Carbon|null $bounced_at
 * @property Carbon|null $unsubscribed_at
 * @property Carbon|null $complained_at
 * @property Carbon|null $opened_at
 * @property Carbon|null $clicked_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property EloquentCollection $failures
 * @property Subscriber $subscriber
 * @property Campaign $source // NOTE(david): this should be updated to a mixed type when Automations are added.
 *
 * @property-read string $source_string
 *
 * @method static MessageFactory factory
 */
class Message extends BaseModel
{
    use HasFactory;

    // NOTE(david): we require this because of namespace issues when resolving factories from models
    // not in the default `App\Models` namespace.
    protected static function newFactory()
    {
        return MessageFactory::new();
    }

    protected $table = 'sendportal_messages';

    /** @var array */
    protected $guarded = [];

    /** @var array */
    public $dates = [
        'queued_at',
        'sent_at',
        'delivered_at',
        'bounced_at',
        'unsubscribed_at',
        'complained_at',
        'opened_at',
        'clicked_at',
        'created_at',
        'updated_at',
    ];

    // We can't use boolean fields on this model because we have multiple points to update from the controller.
    /** @var array */
    protected $booleanFields = [];

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->hash = $model->hash ?: Uuid::uuid4()->toString();
        });

        static::deleting(function (self $message) {
            $message->failures()->delete();
        });
    }

    public function failures(): HasMany
    {
        return $this->hasMany(MessageFailure::class)
            ->orderBy('failed_at');
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }

    /**
     * Get the owning sourceable model.
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Determine if this message is for an automation.
     *
     * @return bool
     */
    public function isAutomation(): bool
    {
        return Helper::isPro() && $this->source_type === AutomationSchedule::class;
    }

    /**
     * Determine if this message is for a campaign.
     *
     * @return bool
     */
    public function isCampaign(): bool
    {
        return $this->source_type === Campaign::class;
    }

    /**
     * Return the string for the source_type.
     *
     * @return string|null
     */
    public function getSourceStringAttribute(): ?string
    {
        if ($this->source_type === Campaign::class) {
            return 'Campaign';
        }

        if (Helper::isPro() && $this->source_type === AutomationSchedule::class) {
            return 'Automation';
        }

        return null;
    }
}
