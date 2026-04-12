<?php

namespace App\Traits;

use App\Models\EmailTemplate;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Blade;

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
            subject: $template ? Blade::render($template->subject, $this->templateData()) : $this->defaultSubject(),
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
                htmlString: Blade::render($template->content, $this->templateData()),
            );
        }

        return new Content(
            markdown: $this->defaultMarkdownView(),
        );
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
