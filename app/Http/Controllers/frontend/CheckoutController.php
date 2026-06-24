<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use App\Models\Order_item;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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

        $address = Address::where('user_id', Auth::user()->id)
            ->where('is_default', 1)->first();

        $user_id = Auth::id();
        $coupon_id = null;
        if(Session::has('coupon')){

            $coupon_id = Session::get('coupon')['coupon_id'];
        }


        if(!$address){
            $request->validate([
                'name' => 'required|max:100',
                'phone' => 'required|numeric|digits:11',
                'zip' => 'required|numeric|digits:6',
                'state' => 'required',
                'city' => 'required',
                'address' => 'required',
                'locality' => 'required',
                'landmark' => 'required'
            ]);
            $address = new Address();

            $address->name = $request->name;
            $address->phone = $request->phone;
            $address->zip = $request->zip;
            $address->state = $request->state;
            $address->city = $request->city;
            $address->address = $request->address;
            $address->locality = $request->locality;
            $address->landmark = $request->landmark;
            $address->country = 'Bangladesh';
            $address->user_id = $user_id;
            $address->is_default = true;
            $address->save();
        }

        $this->setAmountforCheckout();

        $order = new Order();

        $order->user_id = $user_id;
        $order->coupon_id  = $coupon_id;
        $order->subtotal = Session::get('checkout')['subtotal'];
        $order->discount = Session::get('checkout')['discount'];
        $order->tax = Session::get('checkout')['tax'];
        $order->total = Session::get('checkout')['total'];
        $order->name = $address->name;
        $order->phone = $address->phone;
        $order->locality = $address->locality;
        $order->address = $address->address;
        $order->city = $address->city;
        $order->state = $address->state;
        $order->country = $address->country;
        $order->landmark = $address->landmark;
        $order->zip = $address->zip;
        $order->save();

        foreach($cartItems as $item){
            $orderItem = new Order_item();

            $orderItem->product_id = $item['id'];
            $orderItem->order_id = $order->id;
            $orderItem->price = $item['price'];
            $orderItem->quantity = $item['quantity'];
            $orderItem->save();
        }
        if($request->mode == 'cod'){

            $transaction = new Transaction();

            $transaction->user_id = $user_id;
            $transaction->order_id = $order->id;
            $transaction->status = 'pending';
            $transaction->mode = $request->mode;
            $transaction->save();
        }elseif($request->mode == 'card'){
            //

        }elseif($request->mode == 'paypal'){
            //

        }

    // ?Remove Coupon Session

        session()->forget('cart');

        Session::forget('coupon');
        Session::forget('discounts');
        Session::forget('checkout');
        Session::put('order_id',$order->id);

        return redirect()->route('order_confirmation');



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




    public function setAmountforCheckout(){

        $subtotal = collect(session('cart', []))->sum(fn($item) => $item['price'] * $item['quantity']);

        if(count(session('cart', [])) == 0){
            Session::forget('checkout');
            return;
        }

        if(Session::has('coupon')){
            Session::put('checkout', [
                'discount' => Session::get('discounts')['discount'],
                'subtotal' => Session::get('discounts')['subtotal'],
                'tax' => Session::get('discounts')['tax'],
                'total' => Session::get('discounts')['total'],
            ]);
        }else{
            Session::put('checkout', [
                'discount' => 0,
                'subtotal' => $subtotal,
                'tax' => $subtotal * config('cart.tax') / 100,
                'total' => $subtotal + ($subtotal * config('cart.tax') / 100),
            ]);
        }
    }

    public function order_confirmation(){
        if(Session::has('order_id')){
            $order = Order::findOrFail(Session::get('order_id'));
            return view('order_confirmation',compact('order'));
        }
        return redirect()->route('cart.index');
    }



}
