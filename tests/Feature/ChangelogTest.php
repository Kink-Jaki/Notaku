<?php

namespace Tests\Feature;

use App\Support\Changelog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangelogTest extends TestCase
{
    use RefreshDatabase;

    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = tempnam(sys_get_temp_dir(), 'changelog').'.md';
    }

    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }

        parent::tearDown();
    }

    public function test_changelog_utama_diparse_dari_berkas(): void
    {
        $entries = Changelog::entries();

        $this->assertNotEmpty($entries);
        $this->assertSame('1.4.0', $entries[0]['version']);
        $this->assertSame('Personalization & Mobile UI', $entries[0]['title']);
        $this->assertSame('success', $entries[0]['badge']);
        $this->assertSame('Added', $entries[0]['sections'][0]['type']);
        $this->assertSame('warning', $entries[1]['badge']);
        $this->assertSame('Fixed', $entries[1]['sections'][0]['type']);
    }

    public function test_append_menambah_entri_dan_menaikkan_versi(): void
    {
        copy(base_path('CHANGELOG.md'), $this->path);

        $entry = Changelog::append('feat: tambah placeholder brand', $this->path);

        $this->assertNotNull($entry);
        $this->assertSame('1.5.0', $entry['version']);
        $this->assertSame('Added', $entry['section']);

        $entries = Changelog::entries($this->path);
        $this->assertSame('1.5.0', $entries[0]['version']);
        $this->assertSame('tambah placeholder brand', $entries[0]['sections'][0]['items'][0]);
        $this->assertSame('1.4.0', $entries[1]['version']);

        $entry = Changelog::append('fix: perbaikan kecil', $this->path);
        $this->assertSame('1.5.1', $entry['version']);
        $this->assertSame('Fixed', $entry['section']);
    }

    public function test_append_melewati_merge_dan_duplikat(): void
    {
        copy(base_path('CHANGELOG.md'), $this->path);

        $this->assertNull(Changelog::append('Merge branch \'main\' into dev', $this->path));
        $this->assertNull(Changelog::append('fix: tidak dicatat [skip changelog]', $this->path));
        $this->assertNull(Changelog::append('', $this->path));

        $versiAwal = Changelog::entries($this->path);
        Changelog::append('fix: perbaikan footer', $this->path);
        $this->assertNull(Changelog::append('fix: perbaikan footer', $this->path));

        $this->assertCount(count($versiAwal) + 1, Changelog::entries($this->path));
    }

    public function test_pusat_bantuan_menampilkan_changelog_dari_berkas(): void
    {
        $this->get('/pusat-bantuan')
            ->assertOk()
            ->assertSee('id="collapseChangelog"', false)
            ->assertSee('1.4.0')
            ->assertSee('Personalization & Mobile UI')
            ->assertSee('Perbaikan konten & footer Marketplace');
    }
}
