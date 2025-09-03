<?php

namespace App\Http\Controllers;

use App\Models\Delivery;
use App\Models\DeliveryMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class DeliveryController extends Controller
{
    // public function list(Request $request)
    // {
    //     if ($request->ajax()) {
    //         $user = Auth::user();
    //         $data = $user->deliveryMessages()->with('creator')->get();

    //         return DataTables::of($data)
    //             ->addColumn('sender_email', function ($data) {
    //                 return $data->creator->email ?? '';
    //             })
    //             ->toJson();
    //     }
    //     return view('delivery-messages');
    // }

    public function list(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();

            $data = $user->deliveryMessages()
                ->with('creator')
                ->select('delivery_messages.*', 'delivery_message_users.is_read')
                ->get();

            return DataTables::of($data)
                ->addColumn('sender_email', function ($data) {
                    return $data->creator->email ?? '';
                })
                ->toJson();
        }

        $unreadCount = Auth::user()->deliveryMessages()
            ->wherePivot('is_read', 0)
            ->count();

        return view('delivery-messages', compact('unreadCount'));
    }

    public function markRead(Request $request)
    {
        $ids = $request->input('ids', []);
        $user = Auth::user();

        if (!empty($ids)) {
            foreach ($ids as $id) {
                $user->deliveryMessages()->updateExistingPivot($id, ['is_read' => 1]);
            }
        }

        return response()->json(['status' => true]);
    }


}
