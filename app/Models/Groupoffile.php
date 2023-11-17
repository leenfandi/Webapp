<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Groupoffile extends Model
{
    use HasFactory;
    protected $fillable = [
        'file_id',
        'group_id',

    ];
    /**
     * Get the groups that owns the Groupoffile
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function groups(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
    /**
     * Get the files that owns the Groupoffile
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function file()
    {
        return $this->belongsTo(File::class);
    }

     

}

