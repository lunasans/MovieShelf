<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class XmlImportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        // Assuming your setup uses Spatie Permissions or a simple admin boolean. 
        // We will just act as standard user if auth is enough, or setup admin.
        $this->admin = User::factory()->create(); 
    }

    public function test_index_shows_xml_files()
    {
        Storage::fake('local');
        Storage::disk('local')->put('admin/xml/test.xml', '<DVD></DVD>');

        $response = $this->actingAs($this->admin)->get('/admin/import');
        
        $response->assertStatus(200);
        $response->assertViewHas('xmlFiles');
    }

    public function test_import_fails_on_empty_xml()
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->createWithContent('empty.xml', '');

        $response = $this->actingAs($this->admin)->post('/admin/import', [
            'xml_file' => $file,
        ]);

        $response->assertSessionHas('error');
    }

    public function test_import_processes_valid_xml()
    {
        Storage::fake('local');
        
        $xmlContent = '<?xml version="1.0" encoding="UTF-8"?>
            <Collection>
                <DVD>
                    <ID>000000001</ID>
                    <CollectionNumber>1</CollectionNumber>
                    <Title>Test Movie</Title>
                    <ProductionYear>2023</ProductionYear>
                </DVD>
            </Collection>';
            
        $file = UploadedFile::fake()->createWithContent('data.xml', $xmlContent);

        $response = $this->actingAs($this->admin)->post('/admin/import', [
            'xml_file' => $file,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('movies', [
            'id' => 1,
            'title' => 'Test Movie',
            'year' => 2023,
        ]);
    }
}
