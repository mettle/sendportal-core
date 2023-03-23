<?php

declare(strict_types=1);

namespace Sendportal\Base\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialconnectorUser extends Model
{
    use HasFactory;

//    protected $connection = 'solana_socialconnector';
    protected $table = 'socialconnector_users';

    // public $timestamps = false;
    protected $guarded = [];
}
