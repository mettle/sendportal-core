<?php

declare(strict_types=1);

namespace Sendportal\Base\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Ramsey\Uuid\Uuid;
use Sendportal\Automations\Models\AutomationSchedule;

class Message extends BaseModel
{
    /** @var array */
    protected $guarded = [];

    /** @var array */
    public $dates = [
        'created_at',
        'updated_at',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
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
        return \Sendportal\Base\Facades\Helper::isPro() && $this->source_type === AutomationSchedule::class;
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

        if (\Sendportal\Base\Facades\Helper::isPro() && $this->source_type === AutomationSchedule::class) {
            return 'Automation';
        }

        return null;
    }
}
