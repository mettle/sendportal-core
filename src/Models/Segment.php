<?php

declare(strict_types=1);

namespace Sendportal\Base\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Segment extends Model
{
    use HasFactory;

    protected $connection = 'universalroles';

    protected $table = 'segments';
}
