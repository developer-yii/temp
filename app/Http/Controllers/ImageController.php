<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Image;
use App\Models\User;
use App\Models\UserImage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImageController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'file_name' => 'required|max:255',
            'files' => 'required',
            'files.*' => 'max:10240',
        ], [
            'files.*.max' => 'The files may not be greater than 10 MB.',
            'files.required' => 'File is required',
        ]);

        if ($validatedData->fails()) {
            $result = ['status' => false, 'errors' => $validatedData->errors()];
            return response()->json($result);
        }

        //insert new file
        $dir = "public/uploaded_images/";
        $userid = Auth::user()->id;

        $imageLinks = [];
        $imageIds = [];

        foreach ($request->file('files') as $image) {
            $token = Str::random(10);
            while (Image::where('short_link_token', $token)->exists()) {
                $token = Str::random(10);
            }

            $image_name = $image->getClientOriginalName();
            $unique_image_name = time() . '_' . $image->getClientOriginalName();
            $filePath = $dir . $unique_image_name;
            Storage::disk("local")->put($filePath, File::get($image));
            $image = new Image();
            $image->file_name = $request->file_name;
            $image->image_name = $image_name;
            $image->image_path = $unique_image_name;
            $image->user_id = $userid;
            $image->short_link_token = $token;
            if (isset($request->password)) {
                $image->password = md5($request->password);
            }
            $image->save();

            $imageLink = route('image.action', ['token' => $token]);
            $imageLinks[] = $imageLink;
            $imageIds[] = $image->id;
        }

        $result = [
            'status' => true,
            'message' => "Image uploaded successfully.",
            'imageLinks' => $imageLinks,
            'imageIds' => $imageIds,
        ];

        return response()->json($result);
    }
    public function list(Request $request)
    {
        if ($request->ajax()) {
            $authId = Auth::id();
            $userCreateImages = Image::where('user_id', $authId);

            $userAssignImages = DB::table('user_images')
                ->join('images', 'user_images.image_id', '=', 'images.id')
                ->where('user_images.user_id', $authId)
                ->select('images.*');

            $data = $userCreateImages->union($userAssignImages)->get();

            return DataTables::of($data)
                ->addColumn('created_at_formatted', function ($userImage) {
                    $createdAt = new \DateTime($userImage->created_at);
                    return $createdAt->format('d-m-Y h:i A');
                })
                ->addColumn('action', function ($data) {
                    // $copyUrl = asset('image_action/' . $data->short_link_token);
                    $copyUrl = route('image.action', ['token' => $data->short_link_token]) ;
                    return '<a href="javascript:void(0);" class="btn btn-sm btn-danger mr-5 delete-image" data-id="' . $data->id . ' "title="Delete"><i class="fas fa-trash"></i></a><a href="javascript:void(0);" class="btn btn-sm btn-info mr-1 copy-url" data-url="' . $copyUrl . ' "title="Copy Url"><i class="fas fa-copy"></i></a>';
                })
                ->addColumn('image', function ($row) {
                    return $row->getImageUrl();
                })
                ->toJson();
        }
        return view('imagelist');
    }
    public function delete(Request $request)
    {
        $image = Image::find($request->id);
        if (!$image) {
            return response()->json(['message' => 'Image not found'], 404);
        }

        Storage::delete('public/uploaded_images/' . $image->image_path);
        $image->userImages()->delete();
        $image->delete();

        return response()->json(['message' => 'Image deleted successfully']);
    }

    public function deleteMultipleImages(Request $request)
    {
        $ids = $request->input('ids');
        $images = Image::whereIn('id', $ids)->get();

        foreach ($images as $image) {
            Storage::delete('public/uploaded_images/' . $image->image_path);
            $image->userImages()->delete();
            $image->delete();
        }

        $msg = "Images abd Files Delete successfully";
        $result = ["status" => true, "message" => $msg];
        return response()->json($result);
    }

    public function imageAction(Request $request)
    {
        $image = Image::where('short_link_token', $request->token)->first();

        if ($image) {
            $filename = $image->image_path;

            $imagePath = $image->getImageUrl();
            // $imagePath = Storage::url('uploaded_images/' . $filename);
            $filePath = 'public/uploaded_images/' . $filename;

            // if (strpos($imagePath, 'public') == false && config('app.env') != 'local') {
            //     $imagePath = asset('public/storage/uploaded_images/' . $filename);
            // }

            $exists = Storage::disk('local')->exists($filePath);

            if ($exists) {
                return view('downloadimage', compact('image', 'imagePath'));
            } else {
                $image = "";
                return view('downloadimage', compact('image'));
            }
        } else {
            return view('downloadimage', ['image' => null]);
        }
    }

    public function download(Request $request)
    {
        $image = Image::find($request->id);
        if ($image) {
            $filename = $image->image_path;
            $imagePath = asset('storage/uploaded_images/' . $filename);

            if (strpos($imagePath, 'public') == false && config('app.env') != 'local') {
                $imagePath = asset('public/storage/uploaded_images/' . $filename);
            }

            $filepath = 'public/uploaded_images/' . $filename;
            $exists = Storage::disk('local')->exists($filepath);

            if ($exists) {
                if ($image->password != "" || $image->password != null) {
                    if (isset($request->password)) {
                        if ($image->password == md5($request->password)) {
                            $result = [
                                'status' => true,
                                'message' => 'File download successfully',
                                'imagePath' => $imagePath,
                                'filename' => $filename,
                                'imagename' => $image->image_name
                            ];
                        } else {
                            $result = [
                                'status' => false,
                                'message' => 'Please enter valid password',
                            ];
                        }
                    } else {
                        $result = [
                            'status' => false,
                            'message' => 'File is password protected',
                        ];
                    }
                } else {
                    $result = [
                        'status' => true,
                        'message' => 'File Download successfully',
                        'imagePath' => $imagePath,
                        'filename' => $filename,
                        'imagename' => $image->image_name
                    ];
                }
                return response()->json($result);
            } else {
                $result = [
                    'status' => false,
                    'message' => 'File does not exist',
                ];
                return response()->json($result);
            }
        } else {
            $result = [
                'status' => false,
                'message' => 'File does not exist',
            ];
            return response()->json($result);
        }
    }
}
