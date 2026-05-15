<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use App\Models\UserImage;
use App\Models\Conversation;
use App\Models\Image;
use App\Models\InviteUser;
use App\Models\Note;
use App\Models\PinnedMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MessageController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'note' => 'required|string|max:33554432',
            'ttl' => 'required|string',
        ]);

        if ($validatedData->fails()) {
            $response = ['status' => false, 'errors' => $validatedData->errors()];
            return response()->json($response);
        }

        $conversation_token = Str::random(30);
        while (Conversation::where('conversation_token', $conversation_token)->exists()) {
            $conversation_token = Str::random(30);
        }

        $conversation = new Conversation();
        $conversation->conversation_token = $conversation_token;
        $conversation->user_id = Auth::id();
        $conversation->expiry = self::calculateExpiryDate($request->ttl);

        if($conversation->save())
        {
            InviteUser::create([
                'conversation_id' => $conversation->id,
                'user_id'         => auth()->id(),
                'created_by'      => auth()->id(),
            ]);

            $message = new Message();
            $message->user_id = Auth::id();
            $message->conversation_id = $conversation->id;
            $message->message = $request->note;
            // $message->image_ids = $request->imgids;
            $message->created_at = Carbon::now();

            $selectedValue = $request->ttl;
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
            if ($message->save()) {

                $data = Message::find($message->id);
                $image_ids = explode(',', $request->imgids);
                $message_images = [];
                foreach ($image_ids as $image_id) {
                    $shortLinkToken = Image::where('id', $image_id)->value('short_link_token');
                    if ($shortLinkToken && strpos($request->note, $shortLinkToken) !== false) {
                        $user_image = new UserImage();
                        $user_image->user_id = Auth::id();
                        $user_image->image_id = $image_id;
                        $user_image->save();
                        $message_images[] = $image_id;
                    }
                }

                $message_images_ids = implode(',', $message_images);
                $message->image_ids = $message_images_ids;
                $message->save();
                $response = ['status' => true, 'token' => $conversation_token, 'note' => $request->note, 'ttl' => $ttl, 'data' => $conversation];
                return response()->json($response);
            }
        }
    }

    public function delete($token)
    {
        $message = Message::where('url', $token)->first();
        if ($message) {
            $message->delete();
            return redirect()->route('home')->with('success', 'Message deleted successfully.');
        } else {
            return redirect()->route('home')->with('error', 'The message has either been expired/deleted or this URL is invalid.');
        }
    }

    public static function calculateExpiryDate($ttl)
    {
        $timeUnit = substr($ttl, -1);
        $timeValue = intval(substr($ttl, 0, -1));
        $seconds = 0;
        switch ($timeUnit) {
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

    public function reply(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'reply' => 'required|string|max:33554432',
        ]);

        if ($validatedData->fails()) {
            $response = ['status' => false, 'errors' => $validatedData->errors()];
            return response()->json($response);
        }

        $token = $request['token'];
        $conversation = Conversation::where('conversation_token', $token)->first();
        if(!$conversation)
        {
            return view('messageconfirmation')->with('error', 'The message has either been expired/deleted or You are not authorized to access this conversation.');
        }
        $conversation_id = $conversation->id;

        $message = new Message();
        $message->user_id = Auth::id();
        $message->conversation_id = $conversation_id;
        $message->replied_message_id = $request['reply_to_message_id'];
        $message->quoted_text = $request['quoted_text'];
        $message->message = $request['reply'];
        $message->created_at = Carbon::now();

        if ($message->save()) {
            $data = Message::with('user')->find($message->id);
            $data->created_at=date('d-m-Y H:i' , strtotime($data->created_at));
            if($request['imgids']){
                $image_ids = explode(',', $request['imgids']);
                $message_images = [];
                foreach ($image_ids as $image_id) {
                    $shortLinkToken = Image::where('id', $image_id)->value('short_link_token');
                    if ($shortLinkToken && strpos($request->reply, $shortLinkToken) !== false) {
                        $user_image = new UserImage();
                        $user_image->user_id = Auth::id();
                        $user_image->image_id = $image_id;
                        $user_image->save();
                        $message_images[] = $image_id;
                    }
                }

                $message_images_ids = implode(',', $message_images);
                $message->image_ids = $message_images_ids;
                $message->save();
            }


            $response = ['status' => true, 'data' => $data];
            return response()->json($response);
        }
    }

    public function pinMessage(Request $request)
    {
        $message = Message::whereHas('conversation', function ($q) {
            $q->whereHas('invitedUsers', function ($q2) {
                $q2->where('user_id', Auth::id());
            });
        })->find($request->id);

        if (!$message) {
            return response()->json(['status' => false, 'message' => 'Not authorized.']);
        }

        $existing = PinnedMessage::where('user_id', Auth::id())->where('message_id', $message->id)->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['status' => true, 'pinned' => false, 'message' => 'Message unpinned!']);
        }

        PinnedMessage::create([
            'user_id'         => Auth::id(),
            'message_id'      => $message->id,
            'conversation_id' => $message->conversation_id,
            'created_at'      => now(),
        ]);

        return response()->json(['status' => true, 'pinned' => true, 'message' => 'Message pinned!']);
    }

    public function deleteMessage(Request $request)
    {
        $authUserId = Auth::user()->id;
        $message = Message::where('id', $request->id)
                    ->where('user_id', $authUserId)->first();
        if (!$message) {
            $result = ['status' => false, 'message' => 'You do not have access to delete this message.'];
            return response()->json($result);
        }

        try {
            $message->delete();
            $result = ['status' => true, 'message' => 'Message deleted successfully!'];
            return response()->json($result);
        } catch (\Exception $e) {

            return response()->json([
                "message" => "An error occurred while trying to delete the message.",
                "error" => $e->getMessage()
            ], 500);
        }
    }

    public function deleteChat($token)
    {
        $conversation = Conversation::where('conversation_token', $token)->first();

        if (!$conversation) {
            return redirect()->route('home')->with('error', 'Conversation not found.');
        }
        try {
            $conversation->delete();
            return redirect()->route('home')->with('success', 'Conversation deleted successfully.');
        } catch (\Exception $e) {

            return redirect()->route('home')->with('error', 'Error deleting conversation.');
        }
    }

    public function messageRead(Request $request, $token)
    {
        $currenttime = Carbon::now();
        $conversation = Conversation::where('conversation_token', $token)
            ->where('expiry', '>=', $currenttime)
            ->first();

        if (!$conversation) {
            return view('messageconfirmation')->with('error', 'The message has either been expired/deleted or You are not authorized to access this conversation');
        }

        $userId = Auth::id();
        $isInvited = InviteUser::where('conversation_id', $conversation->id)
            ->where('user_id', Auth::id())
            ->exists();

        $firstUser = InviteUser::where('conversation_id', $conversation->id)
            ->where('first_visitor', 1)
            ->first();

        if (!$isInvited) {

            if (!$firstUser) {
                InviteUser::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $userId,
                    'created_by' => $conversation->user_id,
                    'first_visitor' => 1
                ]);
            } else {
                return view('messageconfirmation')->with('error', 'You are not authorized to access this conversation');
            }
        }

        // if (!$isInvited) {
        //     return view('messageconfirmation')->with('error', 'You are not authorized to access this conversation');
        // }

        if ($conversation) {
            $c_token = $conversation->id;
            $authId = Auth::id();
            $data = message::where('messages.conversation_id', $c_token)
                ->join('users', 'users.id', '=', 'messages.user_id')
                ->leftJoin('messages as replied', 'replied.id', '=', 'messages.replied_message_id')
                ->leftJoin('users as replied_user', 'replied_user.id', '=', 'replied.user_id')
                ->leftJoin('pinned_messages', function ($join) use ($authId) {
                    $join->on('pinned_messages.message_id', '=', 'messages.id')
                         ->where('pinned_messages.user_id', '=', $authId);
                })
                ->select(
                    'messages.*',
                    'users.email',
                    'replied.message as replied_message',
                    'replied_user.email as replied_email',
                    DB::raw('IF(pinned_messages.id IS NOT NULL, 1, 0) as is_pinned')
                )
                ->get();

            imagesAssignToUser($data);

            // Get user colors for this conversation
            $userColors = InviteUser::where('conversation_id', $conversation->id)
                ->pluck('color', 'user_id')
                ->toArray();

            // $total_user = Message::where('conversation_id', $conversation->id)
            //     ->distinct()
            //     ->pluck('user_id')
            //     ->toArray();

            // if (in_array(Auth::id(), $total_user) || count($total_user) < 2) {
            //     return view('messageconfirmation', compact('conversation', 'data'));
            // }
            // else {
            //     return view('messageconfirmation')->with('error', 'You are not authorized to access this conversation');
            // }

            return view('messageconfirmation', compact('conversation', 'data', 'userColors'));

        }
        //  else {
        //     return view('messageconfirmation')->with('error', 'The message has either been expired/deleted or You are not authorized to access this conversation');
        // }
    }

    public function myLinks(Request $request)
    {
        $userId = Auth::id();
        $conversations = Conversation::whereHas('invitedUsers', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->with([
                'invitedUsers.user:id,email,nickname',
                'invitedUsers.createdBy:id,email,nickname',
            ])
            ->orderByDesc('created_at')
            ->get();

        return view('mylinks', compact('conversations'));
    }

    public function fetchData(Request $request)
    {
        if(!$request->ajax()) {
            return response()->json(['status' => 404, 'message' => 'Something Went Wrong.', 'data' => []]);
        }

        $currenttime = Carbon::now();
        $conversation = Conversation::where('conversation_token', $request->token)
            ->where('expiry', '>=', $currenttime)
            ->first();

        if (!$conversation) {
            return response()->json(['status' => false, 'data' => '']);
        }

        $c_token = $conversation->id;
        // $query = message::with('user')
        //     ->where('conversation_id', $c_token)
        //     ->join('users', 'users.id', '=', 'messages.user_id')
        //     ->select('messages.id','messages.user_id', 'messages.conversation_id','messages.message','messages.image_ids', 'users.email');


        $query = Message::where('messages.conversation_id', $c_token)
                ->join('users', 'users.id', '=', 'messages.user_id')
                ->leftJoin('invite_users', function ($join) use ($c_token) {
                    $join->on('invite_users.user_id', '=', 'messages.user_id')
                         ->where('invite_users.conversation_id', '=', $c_token);
                })
                ->leftJoin('messages as replied', 'replied.id', '=', 'messages.replied_message_id')
                ->leftJoin('users as replied_user', 'replied_user.id', '=', 'replied.user_id')
                ->leftJoin('invite_users as replied_invite', function ($join) use ($c_token) {
                    $join->on('replied_invite.user_id', '=', 'replied.user_id')
                         ->where('replied_invite.conversation_id', '=', $c_token);
                })
                ->leftJoin('pinned_messages', function ($join) use ($c_token) {
                    $join->on('pinned_messages.message_id', '=', 'messages.id')
                         ->where('pinned_messages.user_id', '=', Auth::id());
                })
                ->select(
                    'messages.id',
                    'messages.user_id',
                    'messages.conversation_id',
                    'messages.message',
                    'messages.image_ids',
                    'messages.created_at',
                    'messages.quoted_text',
                    'users.email',
                    'invite_users.color as user_color',
                    'replied.id as replied_id',
                    'replied.message as replied_message',
                    'replied.user_id as replied_user_id',
                    'replied_user.email as replied_email',
                    'replied_invite.color as replied_user_color',
                    DB::raw('IF(pinned_messages.id IS NOT NULL, 1, 0) as is_pinned')
                );


            if ($request->lastid) {
                $query = $query->where('messages.id', '>', $request->lastid);
            }
            $message = $query->get();

            if(!$message->isEmpty()){
                imagesAssignToUser($message);
                foreach ($message as $value){
                    $created_at = Carbon::parse($value->created_at)->format('d-m-Y H:i');
                    $value->created_at = $created_at;
                }

                // $result = ['status' => true, 'data'=>$message];
                $result = [
                    'status'  => true,
                    'auth_id' => auth()->id(),
                    'data'    => $message
                ];
            }else{
                $result = ['status' => true, 'data'=>''];
            }

        return response()->json($result);
    }

    public function extendsValidity(Request $request)
    {
        $token = $request->token;
        $conversation = Conversation::where('conversation_token', $token)->first();
        if(!$conversation)
        {
            $result = ['status' => false, 'message' => 'Conversation not found', 'data' => null];
            return response()->json($result);
        }

        $validatedData = Validator::make($request->all(), [
            'extend_days' => 'required',
        ]);

        if ($validatedData->fails()) {
            $response = ['status' => false, 'errors' => $validatedData->errors()];
            return response()->json($response);
        }
        else
        {
            $extendsTime = $conversation->no_of_extends;
            if($extendsTime >= 2)
            {
                $result = ['status' => false, 'message' => 'This Converastion Validity already two times extended', 'data' => null];
                return response()->json($result);
            }
            else
            {
                $conversation->no_of_extends += 1;
                $extendDays = $request->extend_days;
                $expiryDate = new \DateTime($conversation->expiry);
                $interval = new \DateInterval('P' . $extendDays . 'D'); // P stands for period, D stands for days
                $expiryDate->add($interval);
                $conversation->expiry = $expiryDate->format('Y-m-d H:i:s');

                if ($conversation->save()) {
                    $result = ['status' => true, 'message' => 'Your Conversation Validity Updated Successfully', 'data' => null];
                    return response()->json($result);
                } else {
                    $result = ['status' => false, 'message' => 'Failed to update Conversation Validity', 'data' => null];
                    return response()->json($result);
                }
            }
        }
    }

    public function inviteUserGet(Request $request)
    {
        $conversationId = $request->conversation_id;
        $inviteUser = InviteUser::with('user:id,email,nickname')
                    ->where('conversation_id', $conversationId)
                    ->where('user_id', '!=', auth()->id())
                    ->where('first_visitor', 0)
                    ->get();

        $firstVisitor = InviteUser::with('user:id,email')
            ->where('conversation_id', $conversationId)
            ->where('first_visitor', 1)
            ->first();

        foreach ($inviteUser as $invite) {
            $invite->has_message = Message::where('conversation_id', $conversationId)
                ->where('user_id', $invite->user_id)
                ->exists();

            $invite->is_creator = ($invite->user_id == $invite->created_by);
        }

        return response()->json([
            'status' => true,
            'inviteUser' => $inviteUser,
            'firstVisitor' => $firstVisitor,
        ]);
    }

    public function inviteUserStore(Request $request)
    {
        $authEmail = strtolower(auth()->user()->email);

        $rules = [];
        foreach (range(1, 5) as $i) {
            $rules["email_$i"] = [
                'nullable',
                'string', // Changed from 'email' to 'string' to allow nicknames
                function ($attribute, $value, $fail) use ($authEmail) {
                    if ($value) {
                        // Check if it's an email or nickname
                        $user = User::where('email', $value)
                            ->orWhere('nickname', $value)
                            ->first();

                        if (!$user) {
                            $fail('The entered user is not registered.');
                        } elseif (strtolower($user->email) === $authEmail) {
                            $fail('You cannot invite yourself.');
                        }
                    }
                },
            ];
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        $usersToInvite = [];
        $userIdsToDelete = [];

        foreach (range(1, 5) as $i) {
            $inputValue = trim($request->input("email_$i"));
            $hiddenId = $request->input("user_id_$i");

            if (!empty($inputValue)) {
                $user = null;

                // 1. Try by ID if present (most reliable from autocomplete)
                if (!empty($hiddenId)) {
                    $user = User::find($hiddenId);
                }

                // 2. Try by Email or Nickname if no ID or ID mismatch
                if (!$user) {
                    $user = User::where('email', $inputValue)
                        ->orWhere('nickname', $inputValue)
                        ->first();
                }

                if ($user) {
                    $usersToInvite[$user->id] = $user;
                    $userIdsToDelete[] = $user->id;
                }
            }
        }

        // Delete users that are being re-invited (or removed if logic was intended for that, 
        // but current logic seems to be "sync" style or just ensuring no dupes on insert)
        if (!empty($userIdsToDelete)) {
            InviteUser::where('conversation_id', $request->conversation_id)
                ->whereIn('user_id', $userIdsToDelete)
                ->delete();
        }
        
        foreach ($usersToInvite as $user) {
             InviteUser::create([
                'conversation_id' => $request->conversation_id,
                'user_id'         => $user->id,
                'created_by'      => auth()->id(),
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Invites processed successfully.',
        ]);
    }

    public function getSuggestableUsers(Request $request)
    {
        if (!auth()->user()->can_get_suggestions) {
            return response()->json([]);
        }

        $query = $request->get('q', '');
        
        $users = User::where('id', '!=', auth()->id())
            ->where('is_block', 0)
            ->where('is_approve', 1)
            ->where(function($q) use ($query) {
                $q->where('email', 'like', '%' . $query . '%')
                  ->orWhere('nickname', 'like', '%' . $query . '%');
            })
            ->select('id', 'email', 'nickname')
            ->limit(10)
            ->get();

        return response()->json($users);
    }

}
