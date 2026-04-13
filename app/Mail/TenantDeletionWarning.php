<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TenantDeletionWarning extends Mailable
{
    use Queueable, SerializesModels, \App\Traits\ManagesEmailTemplates;

    public function __construct(
        public \App\Models\Tenant $tenant,
        public int $inactiveDays,
        public int $daysUntilDeletion
    ) {}

    protected function templateSlug(): string { return 'tenant_deletion_warning'; }

    protected function templateData(): array
    {
        return [
            'tenantId'           => $this->tenant->id,
            'inactiveDays'       => $this->inactiveDays,
            'daysUntilDeletion'  => $this->daysUntilDeletion,
            'loginUrl'           => 'https://' . ($this->tenant->domains->first()?->domain ?? $this->tenant->id),
        ];
    }

    protected function defaultSubject(): string { return 'Dein MovieShelf wird in {{ $daysUntilDeletion }} Tagen gelöscht!'; }

    protected function defaultMarkdownView(): string { return 'emails.tenant-deletion-warning'; }

    public function attachments(): array { return []; }
}
