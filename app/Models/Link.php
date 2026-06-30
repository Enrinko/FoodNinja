<?php

namespace App\Models;

use App\Services\ShortCodeGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Link extends Model
{
    /** @use HasFactory<\Database\Factories\LinkFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'original_url',
        'short_code',
    ];

    protected static function booted(): void
    {
        static::creating(function (Link $link): void {
            if (empty($link->short_code)) {
                $link->short_code = app(ShortCodeGenerator::class)->generate();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function clicks(): HasMany
    {
        return $this->hasMany(Click::class);
    }
}
