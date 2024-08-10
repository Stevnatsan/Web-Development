<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Customer;
use App\Models\Membership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MembershipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function viewRegisterMembership()
    {
        if(Auth::guard('webvendor')->check()){
            $v = Vendor::where('id',Auth::guard('webvendor')->user()->id)->first();
            return view('registermembership',[
                'vendor' => $v
            ]);
        } else{
            return view('registermembership');
        }

    }

}
