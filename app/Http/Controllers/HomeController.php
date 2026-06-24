<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\product;
use App\Models\slide;
use Illuminate\Http\Request;

class HomeController extends Controller
{

    public function index()
    {
        $featuredProducts = Product::where('featured', 1)
                            ->latest()
                            ->paginate(8);

        $categories = Category::orderBy('created_at','DESC')->get();
        $sliders = slide::where('status',1)->get()->take(3);
        $sProducts = product::whereNotNull('sale_price')->where('sale_price','<>','')->inRandomOrder()->get()->take(10);


        return view('index',compact('featuredProducts','categories','sliders','sProducts'));
    }
    public function contactPage(){
        return view('contact');
    }

   public function search(Request $request)
    {
        $query = $request->input('query');

        $results = Product::where('name', 'LIKE', "%{$query}%")
                    ->select('id', 'name', 'slug', 'image')
                    ->take(10)
                    ->get();

        return response()->json($results);
    }
}
