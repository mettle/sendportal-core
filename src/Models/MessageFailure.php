<?php

namespace Sendportal\Base\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageFailure extends BaseModel
{
    protected $guarded = [];

    /**
     * @return BelongsTo
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
