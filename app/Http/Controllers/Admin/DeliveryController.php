<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\DeliveryMessage;
use App\Models\DeliveryMessageUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class DeliveryController extends Controller
{
    public function list(Request $request)
    {
        if($request->ajax())
        {
            $data = DeliveryMessage::with('invitedUsers');
            return DataTables::of($data)
                ->addColumn('user_list', function ($data) {
                    return $data->invitedUsers->pluck('email')->implode('<br>');
                })
                ->addColumn('action', function ($data) {
                return '<center><a href="javascript:void(0);" data-toggle="modal" data-target="#add-modal" id="editmessage" class="btn btn-sm btn-primary mr-1 edit-message" data-id="'.$data->id.'" title="Edit"><i class="mdi mdi-pencil"></i></a><a href="javascript:void(0);" class="btn btn-sm btn-danger mr-1 delete-message" data-id="'.$data->id.'"><i class="mdi mdi-delete" title="Delete"></i></a></center>';
            })

            ->rawColumns(['action', 'user_list'])
            ->toJson();
        }
        return view('admin.delivery');
    }

    public function getUsers(Request $request)
    {
        $search = $request->get('q', '');

        $users = User::query()
            ->where('email', 'like', "%{$search}%")
            ->select('id', 'email')
            ->where('id', "!=", Auth::id())
            ->limit(20)
            ->get();

        return response()->json($users);
    }

    public function addupdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'delivery_message' => 'required|string',
            'email'            => 'required|array|min:1',
            'email.*'          => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status'  => false,'message' => $validator->errors(),'data'    => []]);
        }

        try {
            $deliveryMessage = DeliveryMessage::updateOrCreate(
                ['id' => $request->id],
                [
                    'delivery_message' => $request->delivery_message,
                    'added_by'         => auth()->id(),
                ]
            );

            $syncData = collect($request->email)
                ->mapWithKeys(fn($id) => [(int)$id => ['is_read' => 0]])
                ->toArray();

            $deliveryMessage->invitedUsers()->sync($syncData);
            $message = $request->id
                ? 'Delivery message updated successfully.'
                : 'Delivery message created successfully.';

            return response()->json([
                'status'  => true,
                'message' => $message,
                'data'    => $deliveryMessage->load('invitedUsers'),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong: ' . (app()->environment('local') ? $e->getMessage() : 'Please try again later.'),
                'data'    => []
            ], 500);
        }
    }

    public function details(Request $request)
    {
        $model = DeliveryMessage::with('invitedUsers')->find($request->id);

        $result = ['status' => true, 'message' => '', 'data' => $model];
        return response()->json($result);
    }

    public function delete(Request $request)
    {
        $deliveryMessage = DeliveryMessage::with('invitedUsers')->find($request->id);

        if (! $deliveryMessage) {
            return response()->json(['status'  => false,'message' => 'Record not found.'], 404);
        }

        $deliveryMessage->invitedUsers()->detach();
        $deliveryMessage->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Record and invited users deleted successfully.'
        ]);
    }

}
