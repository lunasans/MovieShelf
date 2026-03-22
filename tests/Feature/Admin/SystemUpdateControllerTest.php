<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Process;
use Tests\TestCase;

class SystemUpdateControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    protected function fakeGitAndNpm()
    {
        Process::fake(function (\Illuminate\Process\PendingProcess $process) {
            $cmd = is_array($process->command) ? implode(' ', $process->command) : $process->command;
            
            if (str_contains($cmd, 'rev-parse --short HEAD')) return Process::result('abcdef');
            if (str_contains($cmd, 'rev-parse --abbrev-ref')) return Process::result('main');
            if (str_contains($cmd, 'log -n 5')) return Process::result("a1b2c3d|Test commit|2 days ago\n");
            if (str_contains($cmd, 'rev-parse @{u}')) return Process::result('remotehash');
            if (str_contains($cmd, 'rev-parse @')) return Process::result('localhash');
            if (str_contains($cmd, 'fetch')) return Process::result('fetched');
            if (str_contains($cmd, 'status --porcelain')) return Process::result('M file.php');
            if (str_contains($cmd, 'stash pop')) return Process::result('popped');
            if (str_contains($cmd, 'stash')) return Process::result('stashed');
            if (str_contains($cmd, 'pull') || str_contains($cmd, 'migrate') || str_contains($cmd, 'config:clear') || str_contains($cmd, 'npm')) return Process::result('done');
            
            return Process::result('');
        });
    }

    public function test_index_displays_update_page()
    {
        $this->fakeGitAndNpm();

        $response = $this->actingAs($this->admin)->get(route('admin.update.index'));

        $response->assertStatus(200);
        $response->assertViewHas('currentCommit', 'abcdef');
        $response->assertViewHas('needsUpdate', true);
    }

    public function test_check_fetches_updates()
    {
        $this->fakeGitAndNpm();

        $response = $this->actingAs($this->admin)->post(route('admin.update.check'));

        $response->assertRedirect(route('admin.update.index'));
        $response->assertSessionHas('success');
    }

    public function test_run_executes_update_commands()
    {
        $this->fakeGitAndNpm();

        $response = $this->actingAs($this->admin)->post(route('admin.update.run'));

        $response->assertRedirect(route('admin.update.index'));
        $response->assertSessionHas('success');
    }

    public function test_index_gracefully_handles_exceptions()
    {
        Process::fake([
            '*' => Process::result('error', 'fatal error', 1)
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.update.index'));
        
        $response->assertStatus(200);
        $response->assertViewHas('error');
    }
}
