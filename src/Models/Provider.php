<?php

declare(strict_types=1);

namespace Sendportal\Base\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Sendportal\Base\Facades\Helper;
use Sendportal\Pro\Models\Automation;

class Provider extends BaseModel
{
    /** @var array */
    protected $fillable = [
        'name',
        'type_id',
        'settings',
    ];

    /**
     * The type of this provider.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(ProviderType::class, 'type_id');
    }

    /**
     * Campaigns using this provider.
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'provider_id');
    }

    /**
     * Automations using this provider.
     */
    public function automations(): HasMany
    {
        return $this->hasMany(Automation::class, 'provider_id');
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
        if  (Helper::isPro()) {
            return (bool)$this->campaigns()->count() + $this->automations()->count();
        }

        return (bool)$this->campaigns()->count();
    }
}
