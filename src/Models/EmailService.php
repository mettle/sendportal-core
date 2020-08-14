<?php

declare(strict_types=1);

namespace Sendportal\Base\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Sendportal\Base\Facades\Helper;
use Sendportal\Pro\Models\Automation;

class EmailService extends BaseModel
{
    /** @var array */
    protected $fillable = [
        'name',
        'type_id',
        'settings',
    ];

    /** @var array */
    protected $casts = [
        'id' => 'int',
        'workspace_id' => 'int',
        'type_id' => 'int'
    ];

    /**
     * The type of this provider.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(EmailServiceType::class, 'type_id');
    }

    /**
     * Campaigns using this provider.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'email_service_id');
    }

    /**
     * Automations using this email service.
     */
    public function automations(): HasMany
    {
        return $this->hasMany(Automation::class, 'email_service_id');
    }

    public function setSettingsAttribute(array $data): void
    {
        $this->attributes['settings'] = encrypt(json_encode($data));
    }

    public function getSettingsAttribute(string $value): array
    {
        return json_decode(decrypt($value), true);
    }

    public function getInUseAttribute(): bool
    {
        if (Helper::isPro()) {
            return (bool)$this->campaigns()->count() + $this->automations()->count();
        }

        return (bool)$this->campaigns()->count();
    }
}
