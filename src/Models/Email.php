<?php

namespace Sendportal\Base\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Email extends BaseModel
{
    /**
     * @var array
     */
    protected $guarded = [];

    /**
     * The mailable relationship.
     *
     * @return MorphTo
     */
    public function mailable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The email's status.
     *
     * @return BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(CampaignStatus::class);
    }

    /**
     * The email's template.
     *
     * @return BelongsTo
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * Get the email's open ratio as an attribute.
     *
     * @return float|int
     */
    public function getOpenRatioAttribute()
    {
        if ($this->attributes['sent_count']) {
            return $this->attributes['open_count'] / $this->attributes['sent_count'];
        }

        return 0;
    }

    /**
     * Get the email's click ratio as an attribute.
     *
     * @return float|int
     */
    public function getClickRatioAttribute()
    {
        if ($this->attributes['click_count']) {
            return $this->attributes['click_count'] / $this->attributes['sent_count'];
        }

        return 0;
    }

    /**
     * Get the full content for this email, including the template content
     *
     * @return string
     */
    public function getFullContentAttribute(): string
    {
        return str_replace('{{content}}', $this->content, $this->template->content);
    }
}
