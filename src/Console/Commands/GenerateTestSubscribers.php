<?php

declare(strict_types=1);

namespace Sendportal\Base\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class GenerateTestSubscribers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sp:subscribers:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test subscribers';

    public function handle()
    {
        $inserts = [];

        $times = 0;
        while ($times < 100) {
            $inserts[] = [
                'workspace_id' => 1,
                'hash' => Uuid::uuid4(),
                'first_name' => 'Steve ' . $times,
                'last_name' => 'Bar ' . $times,
                'email' => 'stevebar545+' . $times . '@gmail.com',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $times++;
        }

        DB::table('sendportal_subscribers')->insert($inserts);
    }
}
