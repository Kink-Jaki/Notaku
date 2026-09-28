<?php

namespace App\Console\Commands;

use App\Support\Changelog;
use Illuminate\Console\Command;

class ChangelogAppend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'changelog:append
        {message : Subjek commit yang akan dicatat}
        {--path= : Lokasi berkas changelog (default: CHANGELOG.md di root)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menambahkan entri changelog dari sebuah commit message';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $entry = Changelog::append($this->argument('message'), $this->option('path'));

        if ($entry === null) {
            $this->components->info('Dilewati: bukan commit yang perlu dicatat di changelog.');

            return self::SUCCESS;
        }

        $this->components->info(sprintf(
            'Entri %s (%s) ditambahkan: %s',
            $entry['version'],
            $entry['section'],
            $entry['subject'],
        ));

        return self::SUCCESS;
    }
}
