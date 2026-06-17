<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Intervention\Image\Laravel\Facades\Image;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = product::orderBy('created_at', 'DESC')->paginate(10);
        return view('admin.products', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();
        return view('admin.add_product', compact('categories', 'brands'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([

            'name' => 'required|',
            'slug' => 'required|unique:products,slug',
            'short_description' => 'required|',
            'description' => 'required|',
            'regular_price' => 'required|',
            'sale_price' => 'required|',
            'SKU' => 'required|',
            'stock_status' => 'required|',
            'featured' => 'required|',
            'quantity' => 'required|',
            'image' => 'required|mimes:png,jpg,jpeg|max:2048',

            'category_id' => 'required|',
            'brand_id' => 'required|',

        ]);
        $product = new product();

        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        $current_timestamp = Carbon::now()->timestamp;

        if ($request->hasFile('image')) {

            $image = $request->file('image');

            $imageName = $current_timestamp . '.' . $image->extension();
            $this->GenerateProductThumbnailImage($image, $imageName);
            $product->image = $imageName;
        }
        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;
        if($request->hasFile('images') ){
            $allowedfileExtion = ['jpg', 'jpeg', 'png'];
            $files = $request->file('images');
            foreach($files as $file){
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array($gextension, $allowedfileExtion);
                if($gcheck){
                    $gfileName = $current_timestamp . "-" . $counter . "." . $gextension;
                    $this->GenerateProductThumbnailImage($file, $gfileName);
                    array_push($gallery_arr, $gfileName);
                    $counter = $counter + 1;
                }

            }
            $gallery_images = implode(',', $gallery_arr);

        }

        $product->images = $gallery_images;
        $product->save();
        return redirect()
            ->route('products.index')
            ->with('status', 'Product added successfully!');



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
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $brands = Brand::select('id', 'name')->orderBy('name')->get();
        $product = product::findOrFail($id);
        return view('admin.edit_product',compact('product','categories','brands'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'slug' => 'required|unique:products,slug,' . $product->id,
            'short_description' => 'required',
            'description' => 'required',
            'regular_price' => 'required',
            'sale_price' => 'required',
            'SKU' => 'required',
            'stock_status' => 'required',
            'featured' => 'required',
            'quantity' => 'required',
            'category_id' => 'required',
            'brand_id' => 'required',
            'image' => 'nullable|mimes:jpg,jpeg,png|max:2048',
        ]);

        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity = $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        if ($request->hasFile('image')) {

            // Delete old image
            if ($product->image) {

                $mainImage = public_path('uploads/products/' . $product->image);
                $thumbImage = public_path('uploads/products/thumbnails/' . $product->image);

                if (file_exists($mainImage)) {
                    unlink($mainImage);
                }

                if (file_exists($thumbImage)) {
                    unlink($thumbImage);
                }
            }

            $image = $request->file('image');
            $imageName = time() . '.' . $image->extension();

            $this->GenerateProductThumbnailImage($image, $imageName);

            $product->image = $imageName;
        }

        // Gallery Images Update
        if ($request->hasFile('images')) {

            // Old gallery images delete
            if ($product->images) {

                $oldGalleryImages = explode(',', $product->images);

                foreach ($oldGalleryImages as $oldImage) {

                    $mainImage = public_path('uploads/products/' . trim($oldImage));
                    $thumbImage = public_path('uploads/products/thumbnails/' . trim($oldImage));

                    if (file_exists($mainImage)) {
                        unlink($mainImage);
                    }

                    if (file_exists($thumbImage)) {
                        unlink($thumbImage);
                    }
                }
            }

            $gallery_arr = [];
            $counter = 1;
            $current_timestamp = Carbon::now()->timestamp;

            foreach ($request->file('images') as $file) {

                $extension = strtolower($file->getClientOriginalExtension());

                if (in_array($extension, ['jpg', 'jpeg', 'png'])) {

                    $gfileName = $current_timestamp . '-' . $counter . '.' . $extension;

                    $this->GenerateProductThumbnailImage($file, $gfileName);

                    $gallery_arr[] = $gfileName;

                    $counter++;
                }
            }

            $product->images = implode(',', $gallery_arr);
        }

        $product->save();

        return redirect()
            ->route('products.index')
            ->with('status', 'Product updated successfully!');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);

        // Main Image Delete
        if ($product->image) {

            $mainImage = public_path('uploads/products/' . $product->image);
            $thumbImage = public_path('uploads/products/thumbnails/' . $product->image);

            if (file_exists($mainImage)) {
                unlink($mainImage);
            }

            if (file_exists($thumbImage)) {
                unlink($thumbImage);
            }
        }

        // Gallery Images Delete
        if ($product->images) {

            $galleryImages = explode(',', $product->images);

            foreach ($galleryImages as $image) {

                $image = trim($image);

                $mainImage = public_path('uploads/products/' . $image);
                $thumbImage = public_path('uploads/products/thumbnails/' . $image);

                if (file_exists($mainImage)) {
                    unlink($mainImage);
                }

                if (file_exists($thumbImage)) {
                    unlink($thumbImage);
                }
            }
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('status', 'Product deleted successfully!');
    }

    public function GenerateProductThumbnailImage($image, $imageName)
    {
        $destinationPath = public_path('uploads/products');
        $thumbnailPath = public_path('uploads/products/thumbnails');

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        if (!file_exists($thumbnailPath)) {
            mkdir($thumbnailPath, 0755, true);
        }

        // Main Image
        $mainImage = Image::read($image->getRealPath());
        $mainImage->cover(540, 689);
        $mainImage->save($destinationPath . '/' . $imageName);

        // Thumbnail Image
        $thumbImage = Image::read($image->getRealPath());
        $thumbImage->cover(104, 104);
        $thumbImage->save($thumbnailPath . '/' . $imageName);
    }
}
