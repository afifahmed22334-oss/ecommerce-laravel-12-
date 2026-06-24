<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\cart;
use App\Models\Coupon;
use App\Models\product;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;


class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cartItems = session()->get('cart', []);
        $subtotal = collect(session('cart', []))->sum(fn($item) => $item['price'] * $item['quantity']);

        return view('carts', compact('cartItems','subtotal'));


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
        $product = Product::findOrFail($request->product_id);

        $cart = session()->get('cart', []);

        if (isset($cart[$product->id])) {

            $cart[$product->id]['quantity'] += $request->quantity;

        } else {

            $cart[$product->id] = [
                'id'       => $product->id,
                'name'     => $product->name,
                'price'    => $product->sale_price ?: $product->regular_price,
                'image'    => $product->image,
                'quantity' => $request->quantity,
            ];
        }

        session()->put('cart', $cart);

        if(Session::has('coupon'))
        {
            $this->calculateDiscount();
        }

        return redirect()->back();
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
        $cart = session()->get('cart', []);

        unset($cart[$id]);

        session()->put('cart', $cart);
        if(Session::has('coupon'))
        {
            $this->calculateDiscount();
        }

        return back();

    }

    public function increase_cart_quantity(string $id){
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {

            $cart[$id]['quantity']++;
            session()->put('cart', $cart);

            if(Session::has('coupon'))
            {
                $this->calculateDiscount();
            }
        }


        return redirect()->back();
    }

    public function decrease_cart_quantity(string $id){
        $cart = session()->get('cart', []);

        if (isset($cart[$id])) {

            if ($cart[$id]['quantity'] > 1) {

                $cart[$id]['quantity']--;

                session()->put('cart', $cart);
            }
            if(Session::has('coupon'))
            {
                $this->calculateDiscount();
            }
        }

        return redirect()->back();
    }

    public function empty_cart(){

        session()->forget('cart');

        // Remove Coupon Session
        Session::forget('coupon');
        Session::forget('discounts');

        return redirect()->back();
    }

    public function removeCoupon (){
        Session::forget('coupon');
        Session::forget('discounts');

        return redirect()->back()->with('success', 'Coupon has been removed!');
    }

    public function apply_coupon_code(Request $request){
        $coupon_code = $request->coupon_code;
        $subtotal = collect(session('cart', []))->sum(fn($item) => $item['price'] * $item['quantity']);



        if(isset($coupon_code)){
            $coupon = Coupon::where('code', $coupon_code)
                ->where('expiry_date', '>=', Carbon::today())
                ->where('cart_value','<=',$subtotal)
                ->first();

            if(!$coupon){
                return redirect()->back()->with('error', 'Invalid coupon code!');
            }else{
                Session::put('coupon', [
                    'coupon_id' => $coupon->id,
                    'code' => $coupon->code,
                    'type' => $coupon->type,
                    'value' => $coupon->value,
                    'cart_value' => $coupon->cart_value
                ]);
                $this->calculateDiscount();

                return redirect()->back()->with('success', 'Coupon has been applied!');

            }


        }else{
            return redirect()->back()->with('error', 'Invalid coupon code!');
        }
    }



    public function calculateDiscount()
{
    if (Session::has('coupon')) {
        $cartSubtotal = collect(session('cart', []))->sum(fn($item) => $item['price'] * $item['quantity']);

        $subtotal = floatval(str_replace(',', '',$cartSubtotal));

        if (Session::get('coupon')['type'] == 'fixed') {

            $discount = Session::get('coupon')['value'];

        } else {

            $discount = ($subtotal * Session::get('coupon')['value']) / 100;
        }

        $subtotalAfterDiscount = $subtotal - $discount;

        $taxAfterDiscount = ($subtotalAfterDiscount * config('cart.tax')) / 100;

        $totalAfterDiscount = $subtotalAfterDiscount + $taxAfterDiscount;

        Session::put('discounts', [
            'discount' => number_format($discount, 2, '.', ''),
            'subtotal' => number_format($subtotalAfterDiscount, 2, '.', ''),
            'tax' => number_format($taxAfterDiscount, 2, '.', ''),
            'total' => number_format($totalAfterDiscount, 2, '.', ''),
        ]);
    }
}



}
