<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cartItems = session()->get('cart', []);
        $subtotal = collect(session('cart', []))->sum(fn($item) => $item['price'] * $item['quantity']);
        $address = Address::where('user_id', Auth::user()->id)
            ->where('is_default', 1)->first();

        return view('checkout', compact('address','cartItems','subtotal'));

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
        $cartItems = session()->get('cart', []);
        $subtotal = collect(session('cart', []))->sum(fn($item) => $item['price'] * $item['quantity']);
        $address = Address::where('user_id', Auth::user()->id)
            ->where('is_default', 1)->first();
        if(!$address){
            // $request->validate([
            //     'name' => 'required',
            //     'name' => 'required',
            //     'name' => 'required',
            //     'name' => 'required',
            //     'name' => 'required',

            // ]);
        }
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
