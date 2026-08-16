<?php

namespace App\Services\Translation;

interface TranslatorDriver
{
    /**
     * Uebersetzt einen Text. $format ist 'text' oder 'html' — bei 'html'
     * muessen Tags erhalten bleiben (FAQ-Antworten und CMS-Inhalte sind HTML).
     *
     * @throws TranslationException
     */
    public function translate(string $text, string $target, ?string $source, string $format): string;
}
