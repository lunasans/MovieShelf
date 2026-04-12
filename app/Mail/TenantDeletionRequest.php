<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantDeletionRequest extends Mailable
{
    use Queueable, SerializesModels, \App\Traits\ManagesEmailTemplates;

    public $tenantName;
    public $deletionUrl;

    public function __construct($tenantName, $deletionUrl)
    {
        $this->tenantName = $tenantName;
        $this->deletionUrl = $deletionUrl;
    }

    protected function templateSlug(): string { return 'tenant_deletion_request'; }

    protected function templateData(): array
    {
        return [
            'tenantName' => $this->tenantName,
            'deletionUrl' => $this->deletionUrl,
        ];
    }

    protected function defaultSubject(): string
    {
        return 'WICHTIG: Bestätigung der Regal-Löschung (' . $this->tenantName . ')';
    }

    protected function defaultMarkdownView(): string { return 'emails.deletion_request'; }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
