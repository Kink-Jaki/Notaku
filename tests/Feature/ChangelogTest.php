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

        // Entri terbaru di-parse dari CHANGELOG.md apa adanya (versi bisa
        // bertambah karena hook post-commit), jadi jangan di-hardcode.
        $terbaru = $entries[0];
        $this->assertArrayHasKey('version', $terbaru);
        $this->assertArrayHasKey('badge', $terbaru);
        $this->assertNotEmpty($terbaru['sections']);

        // Entri 1.4.0 adalah rilis "Personalization & Mobile UI".
        $personalization = collect($entries)->firstWhere('version', '1.4.0');
        $this->assertNotNull($personalization);
        $this->assertSame('Personalization & Mobile UI', $personalization['title']);
        $this->assertSame('success', $personalization['badge']);
        $this->assertSame('Added', $personalization['sections'][0]['type']);

        // Entri_features 1.3.1 memuat bagian Fixed.
        $bugfix = collect($entries)->firstWhere('version', '1.3.1');
        $this->assertNotNull($bugfix);
        $this->assertSame('Fixed', $bugfix['sections'][0]['type']);
    }

    public function test_append_menambah_entri_dan_menaikkan_versi(): void
    {
        copy(base_path('CHANGELOG.md'), $this->path);

        $versiSebelum = Changelog::entries($this->path)[0]['version'];

        $entry = Changelog::append('feat: tambah placeholder brand', $this->path);

        $this->assertNotNull($entry);
        $this->assertSame('Added', $entry['section']);

        // Versi baru harus naik dari versi terbaru sebelumnya, mengikuti
        // jenis entri (`feat:` menaikkan minor, `fix:` menaikkan patch).
        [$major, $minor] = array_map('intval', explode('.', $versiSebelum));
        $this->assertSame("{$major}.".($minor + 1).'.0', $entry['version']);

        $entries = Changelog::entries($this->path);
        $this->assertSame($entry['version'], $entries[0]['version']);
        $this->assertSame('tambah placeholder brand', $entries[0]['sections'][0]['items'][0]);
        $this->assertSame($versiSebelum, $entries[1]['version']);

        $versiFeat = $entry['version'];

        $entry = Changelog::append('fix: perbaikan kecil', $this->path);

        $this->assertNotNull($entry);
        $this->assertSame('Fixed', $entry['section']);

        // `fix:` menaikkan patch dari versi yang baru saja ditulis.
        [$major, $minor, $patch] = array_map('intval', explode('.', $versiFeat));
        $this->assertSame("{$major}.{$minor}.".($patch + 1), $entry['version']);
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
