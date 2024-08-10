<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomerMembershipController extends Controller
{
    public function cancelMembership()
    {
        $c = Customer::where('id',Auth::guard('webcustomer')->user()->id)->first();
        $membership = json_decode($c->customer_membership);
        $membership->status = 'INACTIVE';
        $membership->startPeriod = '';
        $membership->endPeriod = '';
        $membershipData = json_encode($membership);
        DB::table('customers')->where('id', $c->id)->update([
            'customer_membership' => $membershipData
        ]);
        return redirect('customer/profile')->with('message','Membership successfuly cancelled!');
    }

    public function registerMembership(Request $req)
    {
        $rules = [
            'email' => 'required|email:rfc,dns',
            'cardNumber' => 'required|numeric',
            'expiryDate' => 'required',
            'cvv' => 'required | numeric'
        ];

        $validator = Validator::make($req->all(), $rules);

        if($validator->fails()){
            return back()->withErrors($validator);
        }

        $c = Customer::where('id',Auth::guard('webcustomer')->user()->id)->first();
        $startPeriod = Carbon::now();
        $endPeriod = Carbon::now()->addDays(30);
        if(!$c->customer_membership){
            $membershipData = json_encode([
                'status'=> 'ACTIVE',
                'startPeriod'=> $startPeriod,
                'endPeriod' => $endPeriod,
                'discount' => 10
            ]);
        } else{
            $membership = json_decode($c->customer_membership);
            $membership->status = 'ACTIVE';
            $membership->startPeriod = $startPeriod;
            $membership->endPeriod = $endPeriod;
            $membership->discount = 10;
            $membershipData = json_encode($membership);
        }
        DB::table('customers')->where('id', $c->id)->update([
            'customer_membership' => $membershipData
        ]);
        return redirect('customer/profile')->with('message','Sucessfully registered as member!');
    }

}
