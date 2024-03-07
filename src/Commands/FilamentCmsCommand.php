<?php

namespace zaheensayyed\FilamentCms\Commands;

use Illuminate\Console\Command;

class FilamentCmsCommand extends Command
{
    public $signature = 'filament-cms';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
