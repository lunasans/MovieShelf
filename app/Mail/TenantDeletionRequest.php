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
    use Queueable, SerializesModels;

    public $tenantName;
    public $deletionUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($tenantName, $deletionUrl)
    {
        $this->tenantName = $tenantName;
        $this->deletionUrl = $deletionUrl;
    }

    /**
     * Get the message envelope.
     */
    public function Envelope(): Envelope
    {
        return new Envelope(
            subject: 'WICHTIG: Bestätigung der Regal-Löschung (' . $this->tenantName . ')',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.deletion_request',
        );
    }

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
