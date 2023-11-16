<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Groupofuser extends Model
{
    use HasFactory;
    protected $fillable = [

        'user_id',
        'group_id',

    ];
/**
 * Get the user that owns the Groupofuser
 *
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
public function user(): BelongsTo
{
    return $this->belongsTo(User::class );
}
/**
 * Get the groups that owns the Groupofuser
 *
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
public function groups(): BelongsTo
{
    return $this->belongsTo(Groups::class);
}
}
