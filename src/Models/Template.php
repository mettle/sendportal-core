<?php

declare(strict_types=1);

namespace Sendportal\Base\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends BaseModel
{
    /** @var array */
    protected $guarded = [];

    /**
     * Campaigns using this template
     */
    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class);
    }

    public function isInUse(): bool
    {
        return $this->campaigns()->count() > 0;
    }
}
