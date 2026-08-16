<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels, \App\Traits\ManagesEmailTemplates;

    public function __construct(
        public User $user,
        public string $resetUrl
    ) {}

    protected function templateSlug(): string { return 'password_reset'; }

    protected function templateData(): array
    {
        return [
            'user' => $this->user,
            'resetUrl' => $this->resetUrl,
        ];
    }

    protected function defaultSubject(): string { return 'Passwort zurücksetzen'; }

    protected function defaultMarkdownView(): string { return 'emails.password-reset'; }

    public function attachments(): array { return []; }
}
