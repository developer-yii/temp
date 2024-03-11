<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class MessageController extends Controller
{
    public function message(Request $request)
    {
        if($request->ajax())
        {
            $data = Conversation::with('user')->get();
            // $data = DB::table('messages')
            //     ->join('users', 'messages.user_id', '=', 'users.id')
            //     ->select('messages.id','messages.conversation_token')
            //     ->selectRaw('MIN(messages.created_at) as first_message_date')
            //     ->selectRaw('GROUP_CONCAT(DISTINCT users.email SEPARATOR ", ") as user_emails')
            //     ->groupBy('messages.conversation_token')
            //     ->get();

            return DataTables::of($data)
                 ->addColumn('action', function ($data) {
                return '<a href="'.route('admin.view_chat', 'id='.$data->id).'" class="btn btn-sm btn-primary"  data-id="'.$data->id.'"><i class="mdi mdi-eye"></i></a>';
            })

            ->rawColumns(['action', 'status'])
                ->toJson();

        }

        return view('admin.message');
    }
    public function viewchat(Request $request)
    {
        $id=$request->id;
        $messages=Message::with('user')->where('conversation_id', $id)->get();
        if (!$messages)
        {
            return back();
        }

        $uniqueUserEmails = $messages->pluck('user.email')->unique()->toArray();

        $uniqueUserEmailsArray = implode(', ', $uniqueUserEmails);

        return view("admin.view_chat", ['messages' => $messages, 'uniqueUserEmails' => $uniqueUserEmailsArray]);
    }
}
