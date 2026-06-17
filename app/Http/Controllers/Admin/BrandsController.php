<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class BrandsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $brands = Brand::orderBy('id', 'DESC')->paginate(10);
        return view('admin.brands', compact('brands'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.brand_add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required',
            'slug'  => 'required|unique:brands,slug',
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048',
        ]);

        $brand = new Brand();

        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);

        if ($request->hasFile('image')) {

            $image = $request->file('image');

            $fileName = Carbon::now()->timestamp . '.' .
                        $image->getClientOriginalExtension();

            $this->GenerateBrandThumbailsImage($image, $fileName);

            $brand->image = $fileName;
        }

        $brand->save();

        return redirect()
            ->route('brands.index')
            ->with('status', 'Brand has been added successfully!');
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
        $brand = Brand::findOrFail($id);
        return view('admin.edit_brand', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'  => 'required',
            'slug'  => 'required|unique:brands,slug',
            'image' => 'nullable|mimes:png,jpg,jpeg|max:2048',
        ]);

        $brand = Brand::findOrFail($id);

        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);

        if ($request->hasFile('image')) {

            //!delete old image
            if ($brand->image && file_exists(public_path('uploads/brands/' . $brand->image))) {
                unlink(public_path('uploads/brands/' . $brand->image));
            }
            $image = $request->file('image');
            $fileName = Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
            $this->GenerateBrandThumbailsImage($image, $fileName);
            $brand->image = $fileName;
        }

        $brand->save();

        return redirect()
            ->route('brands.index')
            ->with('status', 'Brand has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $brand = Brand::findOrFail($id);

        // Image delete
        if ($brand->image && file_exists(public_path('uploads/brands/' . $brand->image))) {
            unlink(public_path('uploads/brands/' . $brand->image));
        }

        $brand->delete();

        return redirect()
            ->route('brands.index')
            ->with('status', 'Brand deleted successfully!');
    }


    public function GenerateBrandThumbailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/brands');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }
        $img = Image::read($image->getRealPath());

        $img->cover(124, 124);

        $img->save($destinationPath . '/' . $imageName);
    }
}
