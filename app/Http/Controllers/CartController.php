<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function checkProductFromOtherVendor(Product $product){
        $carts = session()->get('cart');
        if(empty($carts)){
            return true;
        }
        else{
            $cart = reset($carts);
            if($product->vendor_id == $cart['vendor_id']){
                return true;
            }
            return false;
        }
    }

    public function addToCart($id){
        $product = Product::find($id);
        $cart = session()->get('cart',[]);
        $discount = null;
        if($product->promotion_id != null){
            $promotion = Promotion::where('id',$product->promotion_id)->first();
            $discount = $promotion->discount;
        }
        if($this->checkProductFromOtherVendor($product)){
            if(isset($cart[$id])){
                $cart[$id]['quantity']++;
            }
            else{
                $cart[$id] = [
                    "name" => $product->name,
                    "quantity" => 1,
                    "price" => $product->price,
                    "product_id"=> $id,
                    "vendor_id"=>$product->vendor_id,
                    "discounted_price"=>$discount,
                    "image"=>$product->image
                ];
            }
            session()->put('cart', $cart);

            return redirect('/checkout');
        }
     }

     public function cartIndex(){
         $carts = session()->get('cart');
         return view('checkout', [
            'carts' => $carts
        ]);
     }

     public function deleteItem($id){

        $cart = session()->get('cart');
        // dd($cart);
        if(isset($cart[$id])){
            unset($cart[$id]);
            session()->put('cart', $cart);
            // dd($cart);
        }
        return redirect('/checkout');
     }

     public function addQuantity($id){
        $cart = session()->get('cart');
        if(isset($cart[$id])){
            $cart[$id]['quantity']++;
            session()->put('cart', $cart);

        }
        return redirect('/checkout');
     }

     public function decreaseQuantity($id){
        $cart = session()->get('cart');
        if(isset($cart[$id])){
            if($cart[$id]['quantity'] > 1){
                $cart[$id]['quantity']--;
            }
            session()->put('cart', $cart);
        }
        return redirect('/checkout');
     }
}
