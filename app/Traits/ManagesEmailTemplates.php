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
            $body = $this->renderTemplateBody($template->content, $this->templateData());
            $html = view('emails.dynamic', ['body' => $body])->render();
            return new Content(htmlString: $html);
        }

        return new Content(
            markdown: $this->defaultMarkdownView(),
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
