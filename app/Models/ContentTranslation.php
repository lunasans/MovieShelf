<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentTranslation extends Model
{
    /**
     * Alle uebersetzbaren Inhalte sind zentrale Redaktionsdaten (FAQ,
     * CMS-Seiten, Screenshots, Mail-Vorlagen) — nie Tenant-Daten. Explizit
     * gepinnt, damit die Uebersetzungen auch bei abweichendem DB_CONNECTION
     * in derselben Datenbank liegen wie EmailTemplate.
     */

    protected $fillable = ['translatable_type', 'translatable_id', 'locale', 'field', 'value'];

    public function translatable(): MorphTo
    {
        return $this->morphTo();
    }
}
