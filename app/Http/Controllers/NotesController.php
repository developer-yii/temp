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
            $data = Note::where('user_id', $authId)
                ->orderBy('pin_note', 'DESC')
                ->orderBy('updated_at', 'DESC')
                ->get();

            return DataTables::of($data)

                ->addColumn('action', function ($data) {
                    $editButton = '<a class="btn btn-sm btn-info mr-5 edit-note" data-id="' . $data->id . '" data-toggle="modal" data-target="#notesModal"><i class="fa fa-pencil"></i></a>';
                    $deleteButton = '<a href="javascript:void(0);" class="btn btn-sm btn-danger mr-5 delete-note" data-id="' . $data->id . '" title="Delete"><i class="fas fa-trash"></i></a>';

                    $pinTitle = $data->pin_note ? 'Unpin Note' : 'Pin Note';
                    if($data->pin_note)
                    {
                        $pinButton = '<a href="javascript:void(0);" class="btn btn-sm btn-warning mr-1 pin-note" data-id="' . $data->id . '" title="' . $pinTitle . '" style="padding: 5px 8px;"><img src="' . asset('images/unpinned-note.png') . '" width="13px"></a>';
                    }
                    else
                    {
                        $pinButton = '<a href="javascript:void(0);" class="btn btn-sm btn-warning mr-1 pin-note" data-id="' . $data->id . '" title="' . $pinTitle . '"><i class="fas fa-thumbtack"></i></a>';
                    }

                    return $editButton . $deleteButton . $pinButton;
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

    public function pin(Request $request)
    {

        $note = Note::find($request->id);
        if (!$note) {
            return response()->json(['message' => 'Note not found'], 404);
        }

        // Toggle pin state
        $note->pin_note = !$note->pin_note;
        $note->save();

        // Return a message based on the new pin state
        if ($note->pin_note) {
            $message = 'Note pinned';
        } else {
            $message = 'Note unpinned';
        }

        return response()->json(['message' => $message]);
    }
}
