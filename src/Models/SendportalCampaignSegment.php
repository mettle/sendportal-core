<?php

declare(strict_types=1);

namespace Sendportal\Base\Models;

use Carbon\Carbon;
use Database\Factories\SegmentFactory;
use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class SendportalCampaignSegment extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

}
