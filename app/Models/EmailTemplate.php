<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;

class EmailTemplate extends Model
{
    use HasTranslations;

    /** Betreff und Inhalt sind mehrsprachig pflegbar (content_translations). */
    protected array $translatable = ['subject', 'content'];


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
        // Uebersetzungen mitladen: Betreff und Inhalt werden anschliessend in
        // der aktiven Locale gelesen, mit Rueckfall auf die Basissprache.
        return self::with('translations')->where('slug', $slug)->first();
    }
}
