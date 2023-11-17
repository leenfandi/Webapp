<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'name',
        'path',
        'status',
       // 'text',
    ];

    /**
     * Get the user that owns the File
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Get all of the groupoffiles for the File
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groupoffiles()
    {
        return $this->hasMany(Groupoffile::class);
    }
}
