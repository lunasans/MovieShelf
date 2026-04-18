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
            return new Content(
                htmlString: $this->interpolateTemplate($template->content, $this->templateData()),
            );
        }

        return new Content(
            markdown: $this->defaultMarkdownView(),
        );
    }

    /**
     * Safely interpolate template variables using only {{ $var }} and {!! $var !!} syntax.
     * Prevents arbitrary Blade/PHP code execution from database-stored templates.
     */
    protected function interpolateTemplate(string $template, array $data): string
    {
        // Handle {{ $object->property }}
        $template = preg_replace_callback('/\{\{\s*\$(\w+)->(\w+)\s*\}\}/', function ($m) use ($data) {
            $obj = $data[$m[1]] ?? null;
            return $obj && is_object($obj) ? e((string) ($obj->{$m[2]} ?? '')) : '';
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
