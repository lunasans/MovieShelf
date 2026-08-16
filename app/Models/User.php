<?php

namespace App\Models;

use App\Mail\PasswordResetMail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements HasLocalePreference
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Sprache fuer Mails an diesen Benutzer. Laravel liest das automatisch,
     * wenn ein User-Model an Mail::to() uebergeben wird — deshalb dort das
     * Model statt der blossen E-Mail-Adresse verwenden.
     */
    public function preferredLocale(): ?string
    {
        return array_key_exists((string) $this->language, config('app.supported_locales', []))
            ? $this->language
            : null;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_admin',
        'two_factor_secret',
        'two_factor_confirmed_at',
        'two_factor_recovery_codes',
        'language',
        'layout',
        'notify_new_episodes',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'notify_new_episodes' => 'boolean',
            'two_factor_secret' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_recovery_codes' => 'encrypted',
        ];
    }

    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_confirmed_at);
    }

    public function sendPasswordResetNotification($token): void
    {
        $resetUrl = url(route('password.reset', ['token' => $token, 'email' => $this->email], false));
        Mail::to($this->email)->send(new PasswordResetMail($this, $resetUrl));
    }

    public function watchedMovies()
    {
        return $this->belongsToMany(Movie::class, 'movie_user_watched');
    }

    public function watchedEpisodes()
    {
        return $this->belongsToMany(Episode::class, 'episode_user_watched')->withPivot('watched_at');
    }
}
