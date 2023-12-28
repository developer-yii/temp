<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class NotesController extends Controller
{

    public function list(Request $request)
    {
        if ($request->ajax()) {
            $authId = Auth::id();
            $data = Note::where('user_id', $authId)->get();


            return DataTables::of($data)

                ->addColumn('action', function ($data) {
                    return '<a class="btn btn-sm btn-info mr-5 edit-note" data-id="' . $data->id . '" data-toggle="modal" data-target="#notesModal"><i class="fa fa-pencil"></i></a><a href="javascript:void(0);" class="btn btn-sm btn-danger mr-1 delete-note" data-id="' . $data->id . ' "title="Delete"><i class="fas fa-trash"></i></a>';
                })
                ->toJson();
        }
        return view('mynotes');
    }

    public function add(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'notes' => 'required',
        ]);

        if ($validatedData->fails()) {
            $response = ['status' => false, 'errors' => $validatedData->errors()];
            return response()->json($response);
        }

        if ($request->note_id) {
            $model = Note::find($request->note_id);
            if ($model) {
                $notes = $model;
                $succssmsg = 'Note updated successfully.';
            } else {
                $result = ['status' => false, 'message' => 'Invalid request', 'data' => []];
                return response()->json($result);
            }
        } else {
            $notes = new Note();
            $succssmsg = 'Note added successfully.';
            $notes->user_id = Auth::id();
            $notes->message = $request->message;
        }
        $notes->note = $request->notes;
        if ($notes->save()) {
            $response = ['status' => true, 'message' => $succssmsg, 'data' => []];
        } else {
            $response = ['status' => false, 'message' => 'Error in saving data', 'data' => []];
        }
        return response()->json($response);
    }

    public function delete(Request $request)
    {
        $note = Note::find($request->id);
        if (!$note) {
            return response()->json(['message' => 'Note not found'], 404);
        }
        $note->delete();

        return response()->json(['message' => 'Note deleted successfully']);
    }

    public function detail(Request $request)
    {
        $data = Note::find($request->id);
        return response()->json($data);
    }
}
