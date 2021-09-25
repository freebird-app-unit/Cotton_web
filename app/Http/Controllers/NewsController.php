<?php

namespace App\Http\Controllers;

use App\Models\News;
use Illuminate\Http\Request;
use Response;
use Validator;
use Yajra\DataTables\Facades\DataTables;
use Storage;
use Image;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $news = News::where('is_delete',1)->get();

            return Datatables::of($news)
            ->setRowId(function ($value) {
                return 'del_'.$value->id;
            })
            ->addIndexColumn()
            ->addColumn('action', function ($value) {
                $edit = route('news_edit',$value->id);
                $delete = route('news_delete',$value->id);
                return  '<a href="'.$edit.'"><i class="fa fa-edit"></i></a>
                <i data-href="'.$delete.'" data-original-title="Delete" data-id="'.$value->id.'" class="fa fa-trash delete-record"></i>';
            })
            ->addColumn('image', function ($value) {
                if(file_exists(storage_path('app/public/news/' . $value->image))){
                    $image = url('storage/app/public/news/'.$value->image);
                    return '<img src="'.$image.'" height="100" width="100" class="img_display">';
                }
            })
            ->rawColumns(['action', 'image'])
            ->make(true);
        }
    	return view('news.list');
    }

    public function add() {
        return view('news.form');
    }

    public function store(Request $request)
    {
        // dd($request->all());

    	$validator = Validator::make($request->all(), [
            'name' =>  'required'
        ]);

        if ($validator->fails()) {
            return Response::json([
                "code" => 200,
                "response_status" => "error",
                "message"         => $validator->errors()->first(),
                "data"            => []
            ]);
        }

        $image_name = "";
        if ($request->hasFile('image')) {
            $path = Storage::disk('public')->getAdapter()->applyPathPrefix('news/'.$request->hidden_image);
            if(file_exists($path)) {
                @unlink($path);
            }
            $image = $request->file('image');
            $image_name = time() . '.' . $image->getClientOriginalExtension();
            $img = Image::make($image->getRealPath());
            $img->stream(); // <-- Key point
            Storage::disk('public')->put('news/' . $image_name, $img, 'public');
        }else{
            $image_name = $request->hidden_image;
        }

        $news = News::updateOrCreate(
            [
                'id' => $request->news_id,
            ],
            [
                'name'    => $request->name,
                'description'    => $request->description,
                'image'    => $image_name
            ]
        );
        $news->save();

        if (!empty($request->news_id)) {
            $message = "Record Update successfully";
        } else {
            $message = "Record Save successfully";
        }

        return Response::json([
            "code" => 200,
            "response_status" => "success",
            "message"         => $message,
            "data"            => []
        ]);
    }

    public function edit($id) {

        $news = [];
        if ((int)$id > 0) {
            $news  = news::find($id);
        }
        return view('news.form',compact(['news']));
    }

    public function destroy($id) {

        $news  = news::find($id);
        $path = Storage::disk('public')->getAdapter()->applyPathPrefix('news/'.$news->image);
        if(file_exists($path)) {
            @unlink($path);
        }

        $news = News::updateOrCreate(
            [
                'id' => $id,
            ],
            [
                'is_delete'    => 0,
            ]
        );
        return Response::json([
            "code" => 200,
            "response_status" => "success",
            "message"         => "Record deleted successfully",
            "data"            => []
        ]);
    }
}
