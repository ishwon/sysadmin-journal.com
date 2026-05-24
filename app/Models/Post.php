<?php

namespace App\Models;

use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    protected $guarded = [];

    /**
     * @return BelongsTo<Gallery, $this>
     */
    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withPivot('sort_order')->orderByPivot('sort_order');
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('sort_order')->orderByPivot('sort_order');
    }

    public function primaryTag(): ?Tag
    {
        return $this->tags->first();
    }

    public function primaryAuthor(): ?User
    {
        return $this->authors->first();
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePosts(Builder $query): Builder
    {
        return $query->where('type', 'post');
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePages(Builder $query): Builder
    {
        return $query->where('type', 'page');
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function getExcerptAttribute(): string
    {
        if ($this->custom_excerpt) {
            return $this->custom_excerpt;
        }

        return Str::limit($this->plaintext ?? '', 200);
    }

    /**
     * Excerpt with HTML stripped — safe for meta tags, search JSON, and JSON-LD.
     */
    public function getPlainExcerptAttribute(): string
    {
        return trim(strip_tags($this->excerpt));
    }

    public static function calculateReadingTime(?string $text): int
    {
        if (! $text) {
            return 1;
        }

        return max(1, (int) round(str_word_count($text) / 200));
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'reading_time' => 'integer',
        ];
    }
}
