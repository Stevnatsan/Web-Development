<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VendorMembershipController extends Controller
{
    public function cancelMembership()
    {
        $v = Vendor::where('id',Auth::guard('webvendor')->user()->id)->first();
        $membership = json_decode($v->vendor_membership);
        $membership->status = 'INACTIVE';
        $membership->startPeriod = '';
        $membership->endPeriod = '';
        $membership->promotionList = '';
        $membershipData = json_encode($membership);
        DB::table('vendors')->where('id', $v->id)->update([
            'vendor_membership' => $membershipData
        ]);
        foreach($v->products as $product){
            $product->promotion_id = NULL;
            $product->save();
        };

        $v->promotions()->delete();

        foreach (session()->all() as $key => $value) {
            if (strpos($key, 'promotion_') === 0) {
                session()->forget($key);
            }
        }

        return redirect('vendor/profile')->with('message','Membership successfuly cancelled!');
    }

    public function registerMembership(Request $req)
    {
        $v = Vendor::where('id',Auth::guard('webvendor')->user()->id)->first();
        $startPeriod = Carbon::now();
        $endPeriod = Carbon::now()->addDays(30);
        if(!$v->vendor_membership){
            $membershipData = json_encode([
                'status' => 'ACTIVE',
                'startPeriod' => $startPeriod,
                'endPeriod' => $endPeriod
            ]);
        } else{
            $membership = json_decode($v->vendor_membership);
            $membership->status = 'ACTIVE';
            $membership->startPeriod = $startPeriod;
            $membership->endPeriod = $endPeriod;
            $membershipData = json_encode($membership);
        }

        DB::table('vendors')->where('id', $v->id)->update([
            'vendor_membership' => $membershipData
        ]);

        $promotionAttributes = [];
        foreach (session()->all() as $key => $value) {
            if (strpos($key, 'promotion_') === 0) {
                $promotionAttributes[$key] = $value;
            }
        }

        $numbers = [];
        foreach ($promotionAttributes as $key => $promo) {
            $number = (int) substr($key, strlen('promotion_'));
            // $p = Product::where('id',$n)->first();
            $promotion = new Promotion();
            $promotion->discount = $promo;
            $promotion->vendor_id = Auth::guard('webvendor')->user()->id;
            $promotion->save();
            Product::where('id',$number)->update(['promotion_id' => $promotion->id]);
        }

        return redirect('vendor/profile')->with('message','Sucessfully registered as member!');
    }
}
