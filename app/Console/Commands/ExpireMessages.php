<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message;
use App\Models\Conversation;
use Carbon\Carbon;

class ExpireMessages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'messages:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete expired messages';

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
     * @return int
     */
    public function handle()
    {
        $currentDateTime = Carbon::now();
        $expiredConversations = Conversation::where('expiry', '<=', $currentDateTime)
            ->get();

        foreach ($expiredConversations as $conversation)
        {
            $conversation->delete();
        }
    }
}
