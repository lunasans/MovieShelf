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
    use Queueable, SerializesModels, \App\Traits\ManagesEmailTemplates;

    public function __construct(
        public Tenant $tenant,
        public User $user,
        public string $tenantUrl
    ) {}

    protected function templateSlug(): string { return 'tenant_activated'; }

    protected function templateData(): array
    {
        return [
            'tenant' => $this->tenant,
            'user' => $this->user,
            'tenantUrl' => $this->tenantUrl,
        ];
    }

    protected function defaultSubject(): string { return 'Dein MovieShelf ist jetzt aktiv!'; }

    protected function defaultMarkdownView(): string { return 'emails.tenant-activated'; }

    public function attachments(): array { return []; }
}
