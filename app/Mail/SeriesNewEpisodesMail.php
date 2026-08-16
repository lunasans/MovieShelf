<?php

namespace App\Mail;

use App\Models\Movie;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * Benachrichtigung an Serien-Follower, wenn der automatische
 * TMDb-Sync neue Episoden zu einer Serie importiert hat.
 */
class SeriesNewEpisodesMail extends Mailable
{
    use Queueable, SerializesModels, \App\Traits\ManagesEmailTemplates;

    public function __construct(
        public User $user,
        public Movie $serie,
        public Collection $newEpisodes,
        public string $seriesUrl,
        public string $unsubscribeUrl = ''
    ) {}

    /**
     * List-Unsubscribe-Header, damit Mail-Clients einen Abmelden-Button anbieten.
     */
    public function headers(): Headers
    {
        return new Headers(
            text: $this->unsubscribeUrl !== ''
                ? ['List-Unsubscribe' => '<'.$this->unsubscribeUrl.'>']
                : [],
        );
    }

    protected function templateSlug(): string { return 'series_new_episodes'; }

    protected function templateData(): array
    {
        $episodeList = $this->newEpisodes
            ->map(fn ($episode) => sprintf(
                'S%dE%d%s',
                $episode->season_number,
                $episode->episode_number,
                $episode->title ? ' – '.$episode->title : ''
            ))
            ->implode("\n- ");

        return [
            'user' => $this->user,
            'seriesTitle' => $this->serie->title,
            'episodeCount' => $this->newEpisodes->count(),
            'episodeList' => '- '.$episodeList,
            'seriesUrl' => $this->seriesUrl,
            'unsubscribeUrl' => $this->unsubscribeUrl,
        ];
    }

    protected function defaultSubject(): string
    {
        return 'Neue Episoden: '.$this->serie->title;
    }

    protected function defaultMarkdownView(): string { return 'emails.series-new-episodes'; }

    public function attachments(): array { return []; }
}
