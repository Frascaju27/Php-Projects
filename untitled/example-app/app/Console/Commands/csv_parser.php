<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\csv_file_parser;
use Illuminate\Support\Facades\Log;

class csv_parser extends Command
{
    protected $signature = "test";

    protected $description = 'run csv parser script';

    public function handle(){
        Log::info('Info - ',['Executing File Parser (artisan test Job)']);

        $job = event(new csv_file_parser());
    }
}
