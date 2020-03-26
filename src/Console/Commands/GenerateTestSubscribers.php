<?php

namespace Sendportal\Base\Console\Commands;

use Illuminate\Console\Command;
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

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
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

        \DB::table('subscribers')->insert($inserts);
    }
}
