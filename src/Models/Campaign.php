<?php

declare(strict_types=1);

namespace Sendportal\Base\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Campaign extends BaseModel
{
    /** @var array */
    protected $guarded = [];

    // We can't use boolean fields on this model because we have multiple points to update from the controller.
    /** @var array */
    protected $booleanFields = [];

    /** @var array */
    protected $casts = [
        'is_open_tracking' => 'bool',
        'is_click_tracking' => 'bool'
    ];

    /**
     * Segments this campaign was sent to.
     */
    public function segments(): BelongsToMany
    {
        return $this->belongsToMany(Segment::class)->withTimestamps();
    }

    /**
     * Status of the campaign.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(CampaignStatus::class);
    }

    /**
     * Template used in the campaign.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * Email Service used for the campaign.
     */
    public function email_service(): BelongsTo
    {
        return $this->belongsTo(EmailService::class);
    }

    /**
     * All of a campaigns's messages.
     */
    public function messages(): MorphMany
    {
        return $this->morphMany(Message::class, 'source');
    }

    /**
     * All of a campaign's opened messages.
     */
    public function opens(): MorphMany
    {
        return $this->morphMany(Message::class, 'source')->whereNotNull('opened_at');
    }

    /**
     * All of the campaign's clicked messages.
     */
    public function clicks(): MorphMany
    {
        return $this->morphMany(Message::class, 'source')->whereNotNull('clicked_at');
    }

    public function getSentCountAttribute(): int
    {
        return $this->messages()->whereNotNull('sent_at')->count();
    }

    public function getSentCountFormattedAttribute(): string
    {
        $value = $this->sent_count;

        if ($value > 999999) {
            return round($value / 1000000) . 'm';
        }

        if ($value > 9999 && $value <= 999999) {
            return round($value / 1000) . 'k';
        }

        return (string)$value;
    }

    /**
     * Get the campaigns's open ratio as an attribute.
     *
     * @return float|int
     * @todo this needs to be refactored, because its running a query per row when list the campaigns
     */
    public function getOpenRatioAttribute()
    {
        if ($openCount = $this->messages()->where('open_count', '>', 0)->count()) {
            return $openCount / $this->sent_count;
        }

        return 0;
    }

    /**
     * Get the campaigns's click ratio as an attribute.
     *
     * @return float|int
     * @todo this needs to be refactored, because its running a query per row when list the campaigns
     */
    public function getClickRatioAttribute()
    {
        if ($clickCount = $this->messages()->where('click_count', '>', 0)->count()) {
            return $clickCount / $this->sent_count;
        }

        return 0;
    }

    /**
     * Get the campaigns's click ratio as an attribute.
     *
     * @return float|int
     * @todo this needs to be refactored, because its running a query per row when list the campaigns
     */
    public function getBounceRatioAttribute()
    {
        if ($bounceCount = $this->messages()->whereNotNull('bounced_at')->count()) {
            return $bounceCount / $this->sent_count;
        }

        return 0;
    }

    /**
     * Get the merged content for this email, including the template content.
     */
    public function getMergedContentAttribute(): ?string
    {
        if ($this->template_id) {
            return str_replace(['{{content}}', '{{ content }}'], $this->content, $this->template->content);
        }

        return $this->content;
    }

    /**
     * Whether the campaign is a draft.
     */
    public function getDraftAttribute(): bool
    {
        return $this->status_id === CampaignStatus::STATUS_DRAFT;
    }

    /**
     * Whether the campaign has been sent.
     */
    public function getQueuedAttribute(): bool
    {
        return $this->status_id === CampaignStatus::STATUS_QUEUED;
    }

    /**
     * Whether the campaign has been sent.
     */
    public function getSendingAttribute(): bool
    {
        return $this->status_id === CampaignStatus::STATUS_SENDING;
    }

    /**
     * Whether the campaign has been sent.
     */
    public function getSentAttribute(): bool
    {
        return $this->status_id === CampaignStatus::STATUS_SENT;
    }

    /**
     * Get the number of unique opens for the campaign.
     */
    public function getUniqueOpenCountAttribute(): int
    {
        return $this->opens()->count();
    }

    /**
     * Get the total number of opens for the campaign.
     */
    public function getTotalOpenCountAttribute(): int
    {
        return (int)$this->opens()->sum('open_count');
    }

    /**
     * Get the number of unique clicks for the campaign.
     */
    public function getUniqueClickCountAttribute(): int
    {
        return $this->clicks()->count();
    }

    /**
     * Get the total number of opens for the campaign.
     */
    public function getTotalClickCountAttribute(): int
    {
        return (int)$this->clicks()->sum('click_count');
    }
}
