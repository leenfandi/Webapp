<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'owner',

    ];
/**
 * Get all of thn groupofusers for the Group
 *
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */
public function groupofusers(): HasMany
{
    return $this->hasMany(Groupofuser::class);
}
}
