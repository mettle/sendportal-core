<?php

declare(strict_types=1);

namespace Sendportal\Base\Models;

use Carbon\Carbon;
use Database\Factories\TemplateFactory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $workspace_id
 * @property string $name
 * @property string|null $content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property EloquentCollection $campaigns
 *
 * @method static TemplateFactory factory
 */
class Template extends BaseModel
{
    use HasFactory;

    protected static function newFactory()
    {
        return TemplateFactory::new();
    }

    /** @var string */
    protected $table = 'sendportal_templates';

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
