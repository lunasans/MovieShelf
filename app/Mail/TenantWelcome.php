<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantWelcome extends Mailable
{
    use Queueable, SerializesModels, \App\Traits\ManagesEmailTemplates;

    public function __construct(
        public \App\Models\Tenant $tenant, 
        public \App\Models\User $user,
        public string $activationUrl,
        public string $tenantUrl
    ) {}

    protected function templateSlug(): string { return 'tenant_welcome'; }

    protected function templateData(): array
    {
        return [
            'tenant' => $this->tenant,
            'user' => $this->user,
            'activationUrl' => $this->activationUrl,
            'tenantUrl' => $this->tenantUrl,
        ];
    }

    protected function defaultSubject(): string { return 'Willkommen bei deinem MovieShelf!'; }

    protected function defaultMarkdownView(): string { return 'emails.tenant-welcome'; }

    public function attachments(): array { return []; }
}
