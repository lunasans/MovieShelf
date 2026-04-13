<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TenantInactivityWarning extends Mailable
{
    use Queueable, SerializesModels, \App\Traits\ManagesEmailTemplates;

    public function __construct(
        public \App\Models\Tenant $tenant,
        public int $inactiveDays
    ) {}

    protected function templateSlug(): string { return 'tenant_inactivity_warning'; }

    protected function templateData(): array
    {
        return [
            'tenantId'    => $this->tenant->id,
            'inactiveDays' => $this->inactiveDays,
            'loginUrl'    => 'https://' . ($this->tenant->domains->first()?->domain ?? $this->tenant->id),
        ];
    }

    protected function defaultSubject(): string { return 'Dein MovieShelf wartet auf dich!'; }

    protected function defaultMarkdownView(): string { return 'emails.tenant-inactivity-warning'; }

    public function attachments(): array { return []; }
}
