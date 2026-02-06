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
                ->where('type', 'delivery')
                ->select('delivery_messages.*', 'delivery_message_users.is_read')
                ->get();

            return DataTables::of($data)
                ->addColumn('sender_email', function ($data) {
                    return $data->creator->email ?? '';
                })
                ->toJson();
        }

        $unreadCount = Auth::user()->deliveryMessages()
            ->where('type', 'delivery')
            ->wherePivot('is_read', 0)
            ->count();

        return view('delivery-messages', compact('unreadCount'));
    }

    public function notificationList(Request $request)
    {
        if ($request->ajax()) {
            $user = Auth::user();

            $data = $user->deliveryMessages()
                ->with(['creator', 'images'])
                ->where('type', 'notification')
                ->select('delivery_messages.*', 'delivery_message_users.is_read')
                ->get();

            return DataTables::of($data)
                ->addColumn('sender_email', function ($data) {
                    return $data->creator->email ?? '';
                })
                ->toJson();
        }

        $unreadCount = Auth::user()->deliveryMessages()
            ->where('type', 'notification')
            ->wherePivot('is_read', 0)
            ->count();

        return view('notifications', compact('unreadCount'));
    }

    public function markRead(Request $request)
    {
        $ids = $request->input('ids', []);
        $type = $request->input('type', 'delivery');
        $user = Auth::user();

        if (!empty($ids)) {
            $user->deliveryMessages()
                ->where('type', $type)
                ->whereIn('delivery_messages.id', $ids)
                ->update(['delivery_message_users.is_read' => 1]);
        }

        return response()->json(['status' => true]);
    }
}
