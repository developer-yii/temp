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
use App\Models\DeliveryMessageImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class DeliveryController extends Controller
{
    public function list(Request $request)
    {
        if ($request->ajax()) {
            $data = DeliveryMessage::with('invitedUsers');
            return DataTables::of($data)
                ->editColumn('type', function ($data) {
                    $labels = [
                        'delivery' => 'Delivery',
                        'notification' => 'Notification',
                    ];
                    return $labels[$data->type] ?? ucfirst($data->type);
                })
                ->addColumn('user_list', function ($data) {
                    return $data->invitedUsers->map(function ($user) {
                        return $user->nickname ? $user->nickname . ' (' . $user->email . ')' : $user->email;
                    })->implode('<br>');
                })
                ->addColumn('action', function ($data) {
                    return '<center><a href="javascript:void(0);" data-toggle="modal" data-target="#add-modal" id="editmessage" class="btn btn-sm btn-primary mr-1 edit-message" data-id="' . $data->id . '" title="Edit"><i class="mdi mdi-pencil"></i></a><a href="javascript:void(0);" class="btn btn-sm btn-danger mr-1 delete-message" data-id="' . $data->id . '"><i class="mdi mdi-delete" title="Delete"></i></a></center>';
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
            ->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('nickname', 'like', "%{$search}%");
            })
            ->select('id', 'email', 'nickname')
            ->where('id', "!=", Auth::id())
            ->limit(20)
            ->get();

        return response()->json($users);
    }

    public function addupdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type'             => 'required|in:delivery,notification',
            'delivery_message' => 'required|string',
            'email'            => 'required|array|min:1',
            'email.*'          => 'required|integer|exists:users,id',
            'files.*'          => 'nullable|mimes:jpeg,png,jpg,gif,pdf|max:512000',
        ], [
            'delivery_message.required' => 'Please enter a message.',
            'email.required' => 'Please select at least one user.',
            'files.*.mimes' => 'The file format must be jpeg, png, jpg, gif, or pdf.',
            'files.*.max'   => 'The uploaded file must not exceed 500MB.'
        ]);

        if ($validator->fails()) {
            return response()->json(['status'  => false, 'message' => $validator->errors(), 'data'    => []]);
        }

        try {
            $deliveryMessage = DeliveryMessage::updateOrCreate(
                ['id' => $request->id],
                [
                    'type'             => $request->type,
                    'delivery_message' => $request->delivery_message,
                    'added_by'         => auth()->id(),
                ]
            );

            if ($request->type === 'notification' && $request->hasFile('files')) {
                foreach ($request->file('files') as $image) {
                    $unique_image_name = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $filePath = 'public/delivery_images/' . $unique_image_name;
                    Storage::disk("local")->put($filePath, File::get($image));

                    DeliveryMessageImage::create([
                        'delivery_message_id' => $deliveryMessage->id,
                        'image_path' => $unique_image_name,
                        'original_name' => $image->getClientOriginalName()
                    ]);
                }
            }

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
                'data'    => $deliveryMessage->load(['invitedUsers', 'images']),
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
        $model = DeliveryMessage::with(['invitedUsers', 'images'])->find($request->id);

        $result = ['status' => true, 'message' => '', 'data' => $model];
        return response()->json($result);
    }

    public function delete(Request $request)
    {
        $deliveryMessage = DeliveryMessage::with(['invitedUsers', 'images'])->find($request->id);

        if (! $deliveryMessage) {
            return response()->json(['status'  => false, 'message' => 'Record not found.'], 404);
        }

        foreach ($deliveryMessage->images as $image) {
            Storage::delete('public/delivery_images/' . $image->image_path);
        }

        $deliveryMessage->invitedUsers()->detach();
        $deliveryMessage->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Record and invited users deleted successfully.'
        ]);
    }
    public function deleteImage(Request $request)
    {
        $image = DeliveryMessageImage::find($request->id);

        if (!$image) {
            return response()->json(['status' => false, 'message' => 'Image not found.'], 404);
        }

        Storage::delete('public/delivery_images/' . $image->image_path);
        $image->delete();

        return response()->json([
            'status' => true,
            'message' => 'Image deleted successfully.'
        ]);
    }
}
