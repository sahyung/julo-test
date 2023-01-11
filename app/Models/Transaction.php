<?php

namespace App\Models;

use App\Traits\UuidsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
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
        'type',
        'amount',
        'reference_id',
    ];

    protected $hidden = ['deleted_at', 'created_at', 'updated_at'];

    /**
     * append accessor to eloquent data
     *
     * @var array
     */
    protected $appends = [
        'deposited_at',
        'withdrawn_at',
        'deposited_by',
        'withdrawn_by',
    ];

    /**
     * Get deposited_at
     *
     * @return string
     */
    public function getDepositedAtAttribute()
    {
        return $this->created_at;
    }

    /**
     * Get withdrawn_at
     *
     * @return string
     */
    public function getWithdrawnAtAttribute()
    {
        return $this->created_at;
    }

    /**
     * Get deposited_by
     *
     * @return string
     */
    public function getDepositedByAttribute()
    {
        return $this->owned_by;
    }

    /**
     * Get withdrawn_by
     *
     * @return string
     */
    public function getWithdrawnByAttribute()
    {
        return $this->owned_by;
    }
}
