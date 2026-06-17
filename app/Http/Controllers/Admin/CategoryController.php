<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Intervention\Image\Laravel\Facades\Image;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::orderBy('id', 'DESC')->paginate(10);
        return view('admin.categories', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        return view('admin.add_category');
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

        $category = new Category();

        $category->name = $request->name;
        $category->slug = Str::slug($request->name);

        if ($request->hasFile('image')) {

            $image = $request->file('image');

            $fileName = Carbon::now()->timestamp . '.' .
                        $image->getClientOriginalExtension();

            $this->GenerateBrandThumbailsImage($image, $fileName);

            $category->image = $fileName;
        }

        $category->save();

        return redirect()
            ->route('categories.index')
            ->with('status', 'Category has been added successfully!');
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
        $category = Category::findOrFail($id);
        return view('admin.edit_category', compact('category'));
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

        $category = Category::findOrFail($id);

        $category->name = $request->name;
        $category->slug = Str::slug($request->name);

        if ($request->hasFile('image')) {

            //!delete old image
            if ($category->image && file_exists(public_path('uploads/categories/' . $category->image))) {
                unlink(public_path('uploads/categories/' . $category->image));
            }
            $image = $request->file('image');
            $fileName = Carbon::now()->timestamp . '.' . $image->getClientOriginalExtension();
            $this->GenerateBrandThumbailsImage($image, $fileName);
            $category->image = $fileName;
        }

        $category->save();

        return redirect()
            ->route('categories.index')
            ->with('status', 'category has been updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);

        // Image delete
        if ($category->image && file_exists(public_path('uploads/categories/' . $category->image))) {
            unlink(public_path('uploads/categories/' . $category->image));
        }

        $category->delete();

        return redirect()
            ->route('categories.index')
            ->with('status', 'category deleted successfully!');
    }


    public function GenerateBrandThumbailsImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/categories');
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }
        $img = Image::read($image->getRealPath());

        $img->cover(124, 124);

        $img->save($destinationPath . '/' . $imageName);
    }
}
