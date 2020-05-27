<?php namespace Sendportal\Base\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Ramsey\Uuid\Uuid;

class Subscriber extends BaseModel
{
    protected $fillable = [
        'hash',
        'email',
        'first_name',
        'last_name',
        'meta',
        'unsubscribed_at',
        'unsubscribe_event_id'
    ];

    protected $dates = [
        'unsubscribed_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->hash = Uuid::uuid4()->toString();
        });
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function segments(): BelongsToMany
    {
        return $this->belongsToMany(Segment::class)->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)
            ->orderBy('id', 'desc');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
