<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use App\Models\UserImage;
use App\Models\Conversation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Session;
use Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(),[
            'note' => 'required|string|max:33554432',
            'ttl' => 'required|string',
        ]);

        if ($validatedData->fails()) 
        {
            $response = ['status' => false,'errors' => $validatedData->errors()];            
            return response()->json($response);
        }

        $conversation_token = Str::random(10);
        while (Message::where('conversation_token', $conversation_token)->exists()) 
        {
            $conversation_token = Str::random(10);
        }

        $url = Str::random(30);
        while (Message::where('url', $url)->exists()) 
        {
            $url = Str::random(30);
        }

        $message = new Message();
        $message->user_id = Auth::id();
        $message->conversation_token = $conversation_token;
        $message->url = $url;
        $message->message = $request['note'];
        $message->image_ids = $request['imgids'];
        $message->expiry = self::calculateExpiryDate($request['ttl']);
        $message->created_at = Carbon::now();

        $selectedValue = $_POST["ttl"];
        $options = array(
            "15m" => "15 minutes",
            "30m" => "30 minutes",
            "45m" => "45 minutes",
            "1h" => "1 hour",
            "6h" => "6 hours",
            "12h" => "12 hours",
            "1d" => "1 day",
            "3d" => "3 days",
            "7d" => "7 days",
            "30d" => "1 month",
            "60d" => "2 months",
        );

        $ttl = $options[$selectedValue];

        
        if ($message->save()) 
        {
            $conversation = New Conversation();
            $conversation->conversation_token = $message->conversation_token;
            $conversation->user_id = Auth::id();
            $conversation->save();

            $data = Message::find($message->id);
            $image_ids = explode(',', $data->image_ids);
            foreach ($image_ids as $image_id) 
            {
                $user_image = new UserImage();
                $user_image->user_id = Auth::id();
                $user_image->image_id = $image_id;
                $user_image->save();
            }

            $response = ['status' => true,'message' => $message, 'ttl'=> $ttl];            
            return response()->json($response);
        }        
    }

    public function delete($token)
    {
        $message = Message::where('url', $token)->first();
        if ($message) 
        {
            $message->delete();
            return redirect()->route('home')->with('success', 'Message deleted successfully.');
        } 
        else
        {
            return redirect()->route('home')->with('error', 'The message has either been read/expired/deleted or this URL is invalid.');  
        }        
    }

    public static function calculateExpiryDate($ttl)
    {
        $timeUnit = substr($ttl, -1); 
        $timeValue = intval(substr($ttl, 0, -1));
        $seconds = 0;
        switch ($timeUnit) 
        {
            case 'm':
                $seconds = $timeValue * 60;
                break;
            case 'h':
                $seconds = $timeValue * 60 * 60;
                break;
            case 'd':
                $seconds = $timeValue * 24 * 60 * 60;
                break;
            default:
                break;
        }

        $expiryTimestamp = time() + $seconds;
        $expiryDate = date('Y-m-d H:i:s', $expiryTimestamp);
        return $expiryDate;
    }

    public function messageConfirm(Request $request, $token)
    {        
           
        $currenttime=Carbon::now();
        $message = message::where('url', $token)
                ->where('link_visit_count', '<', 2)
                ->where('expiry', '>=', $currenttime)
                ->first();
                  
        if ($message) 
        {
            $total_user = Conversation::where('conversation_token', $message->conversation_token)            
                ->pluck('user_id')
                ->toArray();

            if(in_array(Auth::id(), $total_user) || count($total_user) < 2)
            {
                return view('messageconfirmation')->with('message', $message);
            }
            else
            {
                return view('messageconfirmation')->with('error', 'The message URL is invalid.');                
            }
        } 
        else 
        {              
            return view('messageconfirmation')->with('error', 'The message has either been read/expired/deleted or this URL is invalid.');
        }
        
    }

    public function messageRead(Request $request, $token)
    {   
        $currenttime=Carbon::now();
        $message = message::where('url', $token)
            ->where('link_visit_count', '<', 2)
            ->where('expiry', '>=', $currenttime)
            ->first();        
    
        $message->link_visit_count++;
        $message->save();

        $conversation = New Conversation();
        $conversation->conversation_token = $message->conversation_token;
        $conversation->user_id = Auth::id();
        $conversation->save();
        
        if ($message) 
        {   
            $c_token=$message->conversation_token;
            $data = message::where('conversation_token', $c_token)
                ->where('messages.id', '<=', $message->id)
                ->join('users', 'users.id', '=', 'messages.user_id')
                ->select('messages.*', 'users.email')
                ->get();

            $getimg= UserImage::where('user_id', Auth::id())->pluck('image_id')->toArray();

            foreach ($data as $msgdata) 
            {
                $data1 = Message::find($msgdata->id);

                $image_ids = explode(',', $data1->image_ids);
                

                foreach ($image_ids as $image_id) 
                {
                    if(!in_array($image_id, $getimg))
                    {
                        $user_image = new UserImage();
                        $user_image->user_id = Auth::id();
                        $user_image->image_id = $image_id;
                        $user_image->save();
                    }
                }
            }

            $message_html = view('showmessage', compact('message','data'))->render();
             return response()->json(['message_html' => $message_html], 200);
        } 
        else 
        {            
            return view('messageconfirmation')->with('error', 'The message has either been read/expired/deleted or this URL is invalid.');
        }
       
    }
    public function reply(Request $request)
    {
        $validatedData = Validator::make($request->all(),[
            'reply' => 'required|string|max:33554432',
            'ttl' => 'required|string',
        ]);

        if ($validatedData->fails()) 
        {
            $response = ['status' => false,'errors' => $validatedData->errors()];            
            return response()->json($response);
        }

        $selectedValue = $_POST["ttl"];
        $options = array(
            "15m" => "15 minutes",
            "30m" => "30 minutes",
            "45m" => "45 minutes",
            "1h" => "1 hour",
            "6h" => "6 hours",
            "12h" => "12 hours",
            "1d" => "1 day",
            "3d" => "3 days",
            "7d" => "7 days",
            "30d" => "1 month",
            "60d" => "2 months",
        );

        $ttl = $options[$selectedValue];

        $conversation_token = $request['token'];
        
        $url = Str::random(30);
        while (Message::where('url', $url)->exists()) 
        {
            $url = Str::random(30);
        }

        $message = new Message();
        $message->user_id = Auth::id();
        $message->conversation_token = $conversation_token;
        $message->url = $url;
        $message->message = $request['reply'];
        $message->image_ids = $request['imgids'];
        $message->expiry = self::calculateExpiryDate($request['ttl']);
        $message->created_at = Carbon::now();

        if ($message->save()) 
        {
            $data = Message::find($message->id);

            $image_ids = explode(',', $data->image_ids);
            foreach ($image_ids as $image_id) 
            {
                $user_image = new UserImage();
                $user_image->user_id = Auth::id();
                $user_image->image_id = $image_id;
                $user_image->save();
            }

            $response = ['status' => true,'message' => $message, 'ttl' => $ttl ];            
            return response()->json($response);
        }        
    }
    public function deleteChat($token)
    {
      
        $delete = Message::where('conversation_token', $token)->delete();
        if ($delete) 
        {
            return redirect()->route('home')->with('success', 'Message deleted successfully.');
        }          
    }
}

