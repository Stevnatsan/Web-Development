<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class PromotionController extends Controller
{
    public function addPromotion(Product $p, Request $req){
        $rules = [
            'discount' => 'required|numeric'
        ];

        $validator = Validator::make($req->all(), $rules);
        if($validator->fails()){
            return back()->withErrors($validator);
        }


        $discount = $req->input('discount');

        $promotionKey = 'promotion_'.$p->id;
        Session::put($promotionKey, $discount);
        return redirect('/registermembership');
    }

    public function removePromotion(Product $p){
        Session::forget('promotion_'.$p->id);
        return redirect('/registermembership')->with('message','Discount successfuly removed!');
    }
}
