<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;

class EmailTemplate extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'slug',
        'name',
        'subject',
        'content',
        'variables_hint',
    ];

    /**
     * Render the template with the given data.
     */
    public function render(array $data = []): string
    {
        return Blade::render($this->content, $data);
    }

    /**
     * Get a template by slug.
     */
    public static function getBySlug(string $slug): ?self
    {
        return self::where('slug', $slug)->first();
    }
}
