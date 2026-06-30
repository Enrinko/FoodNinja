<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Click extends Model
{
    /** @use HasFactory<\Database\Factories\ClickFactory> */
    use HasFactory;

    /**
     * Clicks are immutable events — only the creation time is tracked.
     */
    public const UPDATED_AT = null;

    protected $fillable = [
        'link_id',
        'ip_address',
        'user_agent',
        'referer',
    ];

    public function link(): BelongsTo
    {
        return $this->belongsTo(Link::class);
    }
}
