<?php

namespace Tests\Unit;

use App\Mail\SeriesNewEpisodesMail;
use App\Models\Episode;
use App\Models\Movie;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeriesNewEpisodesMailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // email_templates liegt auf der central-Connection, die RefreshDatabase
        // nicht migriert – leere Tabelle reicht für den Fallback-Pfad.
        $schema = \Illuminate\Support\Facades\Schema::connection('central');
        if (! $schema->hasTable('email_templates')) {
            $schema->create('email_templates', function ($table) {
                $table->id();
                $table->string('slug');
                $table->string('name')->nullable();
                $table->string('subject')->nullable();
                $table->text('content')->nullable();
                $table->text('variables_hint')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Ohne DB-Template greift die Markdown-Fallback-View; sie muss die
     * berechneten Template-Daten (seriesTitle etc.) übergeben bekommen.
     */
    public function test_renders_default_view_without_db_template()
    {
        $user = new User(['name' => 'Rene']);
        $serie = new Movie(['title' => 'Test Serie']);
        $episode = new Episode(['episode_number' => 2, 'title' => 'Pilot']);
        $episode->season_number = 1;

        $mail = new SeriesNewEpisodesMail(
            $user,
            $serie,
            collect([$episode]),
            'https://demo.movieshelf.info/movies/1',
            'https://demo.movieshelf.info/series/unsubscribe/1?signature=abc'
        );

        $rendered = $mail->render();

        $this->assertStringContainsString('Test Serie', $rendered);
        $this->assertStringContainsString('S1E2', $rendered);
        $this->assertStringContainsString('Pilot', $rendered);
        $this->assertStringContainsString('series/unsubscribe/1', $rendered);
        $this->assertStringContainsString('abmelden', $rendered);
    }

    /**
     * Mit geseedetem DB-Template greift der Template-Pfad inkl. Interpolation.
     */
    public function test_renders_seeded_db_template()
    {
        (new \Database\Seeders\EmailTemplateSeeder())->run();

        $user = new User(['name' => 'Rene']);
        $serie = new Movie(['title' => 'Test Serie']);
        $episode = new Episode(['episode_number' => 2, 'title' => 'Pilot']);
        $episode->season_number = 1;

        $mail = new SeriesNewEpisodesMail(
            $user,
            $serie,
            collect([$episode]),
            'https://demo.movieshelf.info/movies/1',
            'https://demo.movieshelf.info/series/unsubscribe/1?signature=abc'
        );

        $rendered = $mail->render();

        $this->assertStringContainsString('Test Serie', $rendered);
        $this->assertStringContainsString('S1E2', $rendered);
        $this->assertStringContainsString('series/unsubscribe/1', $rendered);
        $this->assertStringContainsString('Zur Serie', $rendered);
    }
}
