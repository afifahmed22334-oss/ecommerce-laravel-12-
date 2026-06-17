<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\cart;
use App\Models\Category;
use App\Models\product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $size = $request->size ?? 12;
        $order = $request->query('order', -1);

        $query = Product::query();

        // Brand Filter
        if ($request->has('brands') && !empty($request->brands)) {
            $query->whereIn('brand_id', $request->brands);
        }

        // Category Filter
        if ($request->filled('categories')) {
            $query->whereIn('category_id', $request->categories);
        }

        // Sorting
        switch ($request->order) {

            case 1:
                $query->orderBy('created_at', 'DESC');
                break;

            case 2:
                $query->orderBy('created_at', 'ASC');
                break;

            case 3:
                $query->orderBy('sale_price', 'ASC');
                break;

            case 4:
                $query->orderBy('sale_price', 'DESC');
                break;

            default:
                $query->orderBy('id', 'DESC');
        }

        $products = $query->paginate($size);

        if ($request->ajax()) {
            return view('partials.shop_products', compact('products'))->render();
        }

        $brands = Brand::withCount('products')->get();
        $categories = Category::withCount('products')->get();



        return view('shop', compact(
                    'products',
                    'brands',
                    'categories',
                    'size',
                    'order'
                ));







    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $product_slug)
    {
        $product = Product::where('slug',$product_slug)->first();
        $rproducts = Product::where('slug','<>',$product_slug)->get()->take(8);

        $cart = cart::where('product_id', $product->id)->first();
        return view('details',compact('product','rproducts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
