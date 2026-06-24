<?php

namespace App\Http\Controllers;

use App\Models\slide;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Intervention\Image\Laravel\Facades\Image;

class SlidesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sliders = slide::orderBy('created_at', 'DESC')->paginate(10);

        return view('admin.sliders', compact('sliders'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.add_slider');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'status' => 'required',
            'image' => 'required',
        ]);

        $slider = new slide();

        $slider->tagline = $request->tagline;
        $slider->title = $request->title;
        $slider->subtitle = $request->subtitle;
        $slider->link = $request->link;
        $slider->status = $request->status;

        $current_timestamp = Carbon::now()->timestamp;
        if ($request->hasFile('image')) {

            $image = $request->file('image');

            $imageName = $current_timestamp . '.' . $image->extension();
            $this->GenerateSliderThumbnailImage($image, $imageName);
            $slider->image = $imageName;
        }
        $slider->save();

        return redirect()
            ->route('sliders.index')
            ->with('status', 'slider added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $slider = slide::findOrFail($id);

        return view('admin.edit_slider', compact('slider'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'status' => 'required'

        ]);

        $slider = slide::findOrFail($id);

        $slider->tagline = $request->tagline;
        $slider->title = $request->title;
        $slider->subtitle = $request->subtitle;
        $slider->link = $request->link;
        $slider->status = $request->status;

        //!delete old image
        if($request->image && file_exists(public_path('uploads/sliders/'.$slider->image))){
            unlink(public_path('uploads/sliders/' . $slider->image));
        }

        $current_timestamp = Carbon::now()->timestamp;
        if ($request->hasFile('image')) {

            $image = $request->file('image');

            $imageName = $current_timestamp . '.' . $image->extension();
            $this->GenerateSliderThumbnailImage($image, $imageName);
            $slider->image = $imageName;
        }
        $slider->save();

        return redirect()
            ->route('sliders.index')
            ->with('status', 'slider has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $slider = slide::findOrFail($id);

        // Image delete
        if ($slider->image && file_exists(public_path('uploads/sliders/' . $slider->image))) {
            unlink(public_path('uploads/sliders/' . $slider->image));
        }

        $slider->delete();

        return redirect()
            ->route('sliders.index')
            ->with('status', 'slider deleted successfully!');
    }


    public function GenerateSliderThumbnailImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/sliders');


        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }



        // Main Image
        $mainImage = Image::read($image->getRealPath());
        $mainImage->cover(400, 690);
        $mainImage->save($destinationPath . '/' . $imageName);
    }
}
