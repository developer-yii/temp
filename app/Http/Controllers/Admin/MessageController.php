<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
           
            $data = DB::table('messages')
                ->join('users', 'messages.user_id', '=', 'users.id')
                ->select('messages.id','messages.conversation_token')
                ->selectRaw('MIN(messages.created_at) as first_message_date')
                ->selectRaw('GROUP_CONCAT(DISTINCT users.email SEPARATOR ", ") as user_emails')
                ->groupBy('messages.conversation_token')                
                ->get();                        

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
        $token=Message::where('messages.id', $id)->first();
        if(!$token) 
        {
            return back();
        }
        $model = Message::select('messages.*', 'users.email as user_email')
            ->leftJoin('users', 'messages.user_id', '=', 'users.id')
            ->where('conversation_token', function ($query) use ($id) {
                $query->select('conversation_token')                      
                      ->from('messages')
                      ->where('id', $id)
                      ->limit(1);
            })
            ->get();     
        
        
        if (!$model) 
        {
            return back();
        }

        $uniqueUserEmails = $model->where('conversation_token', $token->conversation_token)->pluck('user_email')->unique()->toArray();

        $uniqueUserEmailsArray = implode(', ', $uniqueUserEmails);        
        
        return view("admin.view_chat", ['model' => $model, 'uniqueUserEmails' => $uniqueUserEmailsArray]);        
    }
}
