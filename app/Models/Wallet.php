<?php

namespace App\Models;

use App\Traits\UuidsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use HasFactory, SoftDeletes, UuidsTrait;

    /**
     * @var boolean
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'owned_by',
        'status',
        'enabled_at',
        'disabled_at',
        'balance',
        'api_token',
    ];

    protected $hidden = ['api_token', 'deleted_at', 'created_at', 'updated_at'];
}
