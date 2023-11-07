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
        $expiredMessages = Message::where('expiry', '<=', $currentDateTime)
        ->orWhere('link_visit_count', '>=', 2)
        ->get();
        
        foreach ($expiredMessages as $message) 
        {            
            $count_token=Message::where('conversation_token', '=', $message->conversation_token)
                ->whereNotNull('url')
                ->get();
                if($count_token->count()==1 && (isset($count_token[0]) && ($count_token[0]->link_visit_count>=2) || $count_token[0]->expiry<=$currentDateTime))
                {
                    $delete_conversation=Conversation::where('conversation_token', '=', $message->conversation_token)->delete();
                    $delete_all=Message::where('conversation_token', '=', $message->conversation_token)->delete();
                }
                $message->url = Null; // Set the URL field to an empty string          
                $message->save(); 
        }
        //$this->info("delete success");
    }
}
