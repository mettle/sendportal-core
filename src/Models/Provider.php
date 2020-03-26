<?php

namespace Sendportal\Base\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Sendportal\Automations\Models\Automation;

class Provider extends BaseModel
{
    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'type_id',
        'settings',
    ];

    /**
     * ProviderType relationship
     *
     * @param null
     * @return BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(ProviderType::class, 'type_id');
    }

    /**
     * Campaigns relationship
     *
     * @return HasMany
     */
    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'provider_id');
    }

    /**
     * @return HasMany
     */
    public function automations()
    {
        return $this->hasMany(Automation::class, 'provider_id');
    }

    /**
     * @param array $data
     */
    public function setSettingsAttribute(array $data)
    {
        $this->attributes['settings'] = encrypt(json_encode($data));
    }

    /**
     * @param $value
     * @return string
     */
    public function getSettingsAttribute($value)
    {
        return json_decode(decrypt($value), true);
    }

    /**
     * Determine whether or not the provider is currently used by an automation or campaign.
     */
    public function getInUseAttribute()
    {
        if  (\Sendportal\Base\Facades\Helper::isPro()) {
            return $this->campaigns()->count() + $this->automations()->count();
        }

        return $this->campaigns()->count();
    }
}
