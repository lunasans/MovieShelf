<?php

namespace App\Mail;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantActivated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Tenant $tenant,
        public User $user,
        public string $tenantUrl
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Dein MovieShelf ist jetzt aktiv!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tenant-activated',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
