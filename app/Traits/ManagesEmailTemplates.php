<?php

namespace App\Traits;

use App\Models\EmailTemplate;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

trait ManagesEmailTemplates
{
    /**
     * Get the template slug for this mailable.
     */
    abstract protected function templateSlug(): string;

    /**
     * Get the data for the template.
     */
    abstract protected function templateData(): array;

    /**
     * Setzt die Empfaengersprache, bevor Laravel die Mail rendert.
     *
     * Mailable::send() ist der Einstiegspunkt des Mailers; eine Trait-Methode
     * geht der geerbten Klassenmethode vor. Eine am Aufrufort gesetzte Sprache
     * (Mail::to(...)->locale(...)) bleibt unangetastet.
     */
    public function send($mailer)
    {
        if (! $this->locale) {
            $this->locale($this->resolveTemplateLocale());
        }

        return parent::send($mailer);
    }

    /**
     * Empfaengersprache: die des adressierten Benutzers, sonst die im Cadmin
     * eingestellte Standardsprache neuer Regale, sonst der Config-Fallback.
     *
     * Wichtig, weil die meisten Mails aus einem Admin- oder Scheduler-Kontext
     * verschickt werden — die dort aktive Locale ist die des Ausloesenden,
     * nicht die des Empfaengers.
     */
    protected function resolveTemplateLocale(): string
    {
        $supported = config('app.supported_locales', []);

        foreach ($this->templateData() as $value) {
            if ($value instanceof \Illuminate\Contracts\Translation\HasLocalePreference) {
                $preferred = $value->preferredLocale();

                if ($preferred && array_key_exists($preferred, $supported)) {
                    return $preferred;
                }
            }
        }

        // Bewusst ueber die zentrale Verbindung: default_tenant_language ist
        // eine Plattform-Einstellung. Wird die Mail aus einem Regal heraus
        // verschickt, zeigt Setting auf dessen Datenbank — dort gibt es den
        // Schluessel nicht, und die Aufloesung fiele still auf Englisch
        // zurueck, obwohl die Plattform auf Deutsch steht.
        $default = (string) (\App\Models\Setting::on('central')
            ->where('key', 'default_tenant_language')
            ->value('value') ?? '');

        return array_key_exists($default, $supported)
            ? $default
            : (string) config('app.fallback_locale', 'en');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $template = EmailTemplate::getBySlug($this->templateSlug());

        return new Envelope(
            subject: $template ? $this->interpolateTemplate($template->subject, $this->templateData()) : $this->defaultSubject(),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $template = EmailTemplate::getBySlug($this->templateSlug());

        if ($template) {
            $body = $this->renderTemplateBody($template->content, $this->templateData());
            $html = view('emails.dynamic', ['body' => $body])->render();
            return new Content(htmlString: $html);
        }

        return new Content(
            markdown: $this->defaultMarkdownView(),
            with: $this->templateData(),
        );
    }

    /**
     * Safely interpolate template variables using only {{ $var }} and {!! $var !!} syntax.
     * Prevents arbitrary Blade/PHP code execution from database-stored templates.
     */
    protected function renderTemplateBody(string $content, array $data): string
    {
        // Strip <x-mail::message> wrapper
        $content = preg_replace('/<x-mail::message[^>]*>\s*/s', '', $content);
        $content = preg_replace('/\s*<\/x-mail::message>/s', '', $content);

        // Interpolate variables first
        $content = $this->interpolateTemplate($content, $data);

        // Convert <x-mail::button :url="$var"> — resolve variable first, then build HTML
        $content = preg_replace_callback(
            '/<x-mail::button\s+:url="([^"]*)"[^>]*>(.*?)<\/x-mail::button>/s',
            function ($m) use ($data) {
                $url = $m[1];
                // Resolve bare $variable references like $tenantUrl
                $url = preg_replace_callback('/\$(\w+)/', function ($vm) use ($data) {
                    return isset($data[$vm[1]]) && is_scalar($data[$vm[1]]) ? $data[$vm[1]] : $vm[0];
                }, $url);
                return '<p style="text-align:center;margin:2rem 0;">'
                    . '<a href="' . e($url) . '" style="display:inline-block;background:#3b82f6;color:#ffffff;'
                    . 'padding:14px 32px;border-radius:8px;text-decoration:none;font-weight:700;font-size:14px;">'
                    . trim($m[2]) . '</a></p>';
            },
            $content
        );

        // Parse remaining Markdown to HTML
        return \Illuminate\Support\Str::markdown($content);
    }

    protected function interpolateTemplate(string $template, array $data): string
    {
        // Handle {{ $object->property }}
        $template = preg_replace_callback('/\{\{\s*\$(\w+)->(\w+)\s*\}\}/', function ($m) use ($data) {
            $obj = $data[$m[1]] ?? null;

            if (! $obj || ! is_object($obj)) {
                // Frueher wurde hier still eine leere Zeichenkette eingesetzt:
                // Eine Vorlage mit einer Variable, die diese Mail gar nicht
                // mitliefert, sah dann einfach nach "Hallo ," aus, ohne dass
                // irgendwo etwas auffiel. Der Platzhalter im Text bleibt leer,
                // damit der Empfaenger keine Rohdaten sieht — der Hinweis
                // landet stattdessen im Log.
                \Illuminate\Support\Facades\Log::warning('E-Mail-Vorlage nutzt eine nicht verfuegbare Variable', [
                    'template' => $this->templateSlug(),
                    'variable' => '$' . $m[1] . '->' . $m[2],
                    'verfuegbar' => array_keys($data),
                ]);

                return '';
            }

            return e((string) ($obj->{$m[2]} ?? ''));
        }, $template);

        // Handle {{ config('key') }} and {{ config("key") }}
        $template = preg_replace_callback('/\{\{\s*config\([\'"]([^\'"]+)[\'"]\)\s*\}\}/', function ($m) {
            return e((string) (config($m[1]) ?? ''));
        }, $template);

        // Handle simple {{ $key }} and {!! $key !!}
        foreach ($data as $key => $value) {
            if (!is_scalar($value)) continue;
            $escaped = e((string) $value);
            $template = str_replace('{{ $'.$key.' }}', $escaped, $template);
            $template = str_replace('{{$'.$key.'}}', $escaped, $template);
            $template = str_replace('{{ $'.$key.'}}', $escaped, $template);
            $template = str_replace('{{$'.$key.' }}', $escaped, $template);
            $template = str_replace('{!! $'.$key.' !!}', (string) $value, $template);
            $template = str_replace('{!!$'.$key.'!!}', (string) $value, $template);
        }

        return $template;
    }

    /**
     * Define the default subject if no template is found.
     */
    protected function defaultSubject(): string
    {
        return 'MovieShelf Benachrichtigung';
    }

    /**
     * Define the default markdown view if no template is found.
     */
    abstract protected function defaultMarkdownView(): string;
}
