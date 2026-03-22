<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_actors_table_has_slug_column()
    {
        $this->assertTrue(Schema::hasColumn('actors', 'slug'));
    }

    public function test_actors_table_has_expected_columns()
    {
        $this->assertTrue(Schema::hasColumn('actors', 'first_name'));
        $this->assertTrue(Schema::hasColumn('actors', 'last_name'));
        $this->assertTrue(Schema::hasColumn('actors', 'tmdb_id'));
    }
}
