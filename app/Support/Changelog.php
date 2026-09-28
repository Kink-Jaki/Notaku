<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class Changelog
{
    /**
     * Peta tipe commit konvensional ke bagian changelog Keep a Changelog.
     *
     * @var array<string, string>
     */
    private const SECTIONS = [
        'feat' => 'Added',
        'fix' => 'Fixed',
        'perf' => 'Changed',
        'refactor' => 'Changed',
        'docs' => 'Changed',
        'chore' => 'Changed',
        'test' => 'Changed',
        'style' => 'Changed',
        'build' => 'Changed',
        'ci' => 'Changed',
    ];

    /**
     * Warna badge per bagian changelog.
     *
     * @var array<string, string>
     */
    private const SECTION_BADGES = [
        'Added' => 'success',
        'Changed' => 'info',
        'Fixed' => 'warning',
        'Removed' => 'danger',
    ];

    public static function path(): string
    {
        return base_path('CHANGELOG.md');
    }

    /**
     * Warna badge suatu versi ditentukan dari bagian yang dimilikinya.
     *
     * @param  array<int, array{type: string, items: array<int, string>}>  $sections
     */
    public static function badgeFor(array $sections): string
    {
        $types = array_column($sections, 'type');

        if (in_array('Removed', $types, true)) {
            return self::SECTION_BADGES['Removed'];
        }

        if (in_array('Fixed', $types, true) && ! in_array('Added', $types, true)) {
            return self::SECTION_BADGES['Fixed'];
        }

        if (in_array('Added', $types, true)) {
            return self::SECTION_BADGES['Added'];
        }

        return self::SECTION_BADGES['Changed'];
    }

    public static function sectionBadge(string $type): string
    {
        return self::SECTION_BADGES[$type] ?? self::SECTION_BADGES['Changed'];
    }

    /**
     * Konversi format inline markdown minimal menjadi HTML.
     */
    public static function inline(string $text): string
    {
        $safe = e($text);
        $safe = preg_replace('/\*\*([^*\n]+)\*\*/', '<strong>$1</strong>', $safe);
        $safe = preg_replace('/(^|[^*])\*([^*\n]+)\*/', '$1<em>$2</em>', $safe);

        return preg_replace('/`([^`\n]+)`/', '<code>$1</code>', $safe);
    }

    /**
     * Tanggal changelog ditampilkan dalam format "28 Sep 2026".
     */
    public static function formatDate(?string $date): string
    {
        if (! $date) {
            return '';
        }

        try {
            return Carbon::parse($date)->format('d M Y');
        } catch (\Throwable) {
            return $date;
        }
    }

    /**
     * Daftar entri changelog dalam urutan berkas (terbaru di atas).
     *
     * @return array<int, array{version: string, date: string, title: ?string, sections: array<int, array{type: string, items: array<int, string>}>, badge: string}>
     */
    public static function entries(?string $path = null): array
    {
        $path ??= self::path();

        if (! is_file($path)) {
            return [];
        }

        $entries = self::parse((string) file_get_contents($path));

        foreach ($entries as $index => $entry) {
            $entries[$index]['badge'] = self::badgeFor($entry['sections']);
        }

        return $entries;
    }

    /**
     * Tambahkan entri baru dari commit message.
     *
     * Mengembalikan entri yang dibuat, atau null jika commit dilewati
     * (merge commit, "[skip changelog]", atau duplikat saat `git commit --amend`).
     *
     * @return array{version: string, date: string, section: string, subject: string}|null
     */
    public static function append(string $message, ?string $path = null): ?array
    {
        $path ??= self::path();
        $message = trim(preg_replace('/\s+/', ' ', $message) ?? '');

        if ($message === '' || preg_match('/^Merge\b/', $message) || str_contains($message, '[skip changelog]')) {
            return null;
        }

        [$type, $subject, $breaking] = self::parseSubject($message);

        $contents = is_file($path) ? (string) file_get_contents($path) : "# Changelog\n";

        if (self::alreadyLogged($contents, $subject)) {
            return null;
        }

        $version = self::nextVersion($contents, $type, $breaking);
        $section = self::SECTIONS[$type] ?? 'Changed';
        $block = "## [{$version}] - ".date('Y-m-d')."\n\n### {$section}\n- {$subject}\n\n";

        $position = strpos($contents, '## [');
        $updated = $position === false
            ? rtrim($contents)."\n\n".$block
            : substr($contents, 0, $position).$block.substr($contents, $position);

        file_put_contents($path, rtrim($updated)."\n");

        return [
            'version' => $version,
            'date' => date('Y-m-d'),
            'section' => $section,
            'subject' => $subject,
        ];
    }

    /**
     * Pisahkan commit message konvensional menjadi tipe, subjek, dan penanda breaking change.
     *
     * @return array{0: string, 1: string, 2: bool}
     */
    private static function parseSubject(string $message): array
    {
        if (preg_match('/^([a-zA-Z]+)(?:\([^)]*\))?(!)?:\s*(.+)$/', $message, $matches)) {
            return [strtolower($matches[1]), trim($matches[3]), $matches[2] === '!'];
        }

        return ['', $message, false];
    }

    private static function alreadyLogged(string $contents, string $subject): bool
    {
        foreach (self::parse($contents) as $entry) {
            foreach ($entry['sections'] as $section) {
                if (in_array($subject, $section['items'], true)) {
                    return true;
                }
            }
        }

        return false;
    }

    private static function nextVersion(string $contents, string $type, bool $breaking): string
    {
        if (! preg_match('/^##\s*\[v?(\d+)\.(\d+)\.(\d+)\]/m', $contents, $matches)) {
            return $breaking ? '1.0.0' : ($type === 'feat' ? '0.1.0' : '0.0.1');
        }

        [$major, $minor, $patch] = [(int) $matches[1], (int) $matches[2], (int) $matches[3]];

        if ($breaking) {
            return ($major + 1).'.0.0';
        }

        if ($type === 'feat') {
            return $major.'.'.($minor + 1).'.0';
        }

        return $major.'.'.$minor.'.'.($patch + 1);
    }

    /**
     * Parse isi berkas changelog menjadi daftar entri.
     *
     * @return array<int, array{version: string, date: string, title: ?string, sections: array<int, array{type: string, items: array<int, string>}>}>
     */
    private static function parse(string $contents): array
    {
        $entries = [];
        $current = -1;
        $section = -1;

        foreach (preg_split('/\R/', $contents) ?: [] as $line) {
            if (preg_match('/^##\s+\[([^\]]+)\]\s*(?:-\s*(.+?))?\s*$/', $line, $matches)) {
                $entries[] = [
                    'version' => trim($matches[1]),
                    'date' => trim($matches[2] ?? ''),
                    'title' => null,
                    'sections' => [],
                ];
                $current = count($entries) - 1;
                $section = -1;

                continue;
            }

            if ($current < 0) {
                continue;
            }

            if (preg_match('/^###\s+(.+?)\s*$/', $line, $matches)) {
                $entries[$current]['sections'][] = ['type' => trim($matches[1]), 'items' => []];
                $section = count($entries[$current]['sections']) - 1;

                continue;
            }

            if ($section < 0 && preg_match('/^\*\*(.+)\*\*$/', $line, $matches)) {
                $entries[$current]['title'] = trim($matches[1]);

                continue;
            }

            if (preg_match('/^[-*]\s+(.+?)\s*$/', $line, $matches)) {
                if ($section < 0) {
                    $entries[$current]['sections'][] = ['type' => 'Changed', 'items' => []];
                    $section = count($entries[$current]['sections']) - 1;
                }

                $entries[$current]['sections'][$section]['items'][] = trim($matches[1]);
            }
        }

        return $entries;
    }
}
