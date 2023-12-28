<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class NoteController extends Controller
{
    public function notelist(Request $request)
    {

        if($request->ajax())
        {
            $data = Note::all();

            return DataTables::of($data)
                ->addColumn('action', function ($data) {
                return '<center><a href="javascript:void(0);" class="btn btn-sm btn-danger mr-1 delete-note" data-id="'.$data->id.'"><i class="mdi mdi-delete"></i></a></center>';
            })

            ->rawColumns(['action'])
            ->toJson();
        }
        return view('admin.notes');
    }
    public function notedelete(Request $request)
    {
        $user = Note::find($request->id);
        $user->delete();
        $msg = "Records Delete successfully";
        $result = ["status" => true, "message" => $msg];
        return response()->json($result);
    }
}
