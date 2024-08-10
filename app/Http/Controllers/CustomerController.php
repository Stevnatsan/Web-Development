<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\Email;

class CustomerController extends Controller
{
    public function register(Request $req){

        $rules = [
            'name' => 'required|max:50|regex:/^[\pL\s\-]+$/u',
            'phoneNumber' => 'required|regex:/^(0)[0-9]{11}/',
            'email' => 'required|email:rfc,dns|unique:customers',
            'role' => 'required', Rule::in(['CUSTOMER', 'VENDOR']),
            'password' => 'required | min:8 | alpha_num |confirmed'
        ];

        $validator = Validator::make($req->all(), $rules);

        if($validator->fails()){
            return back()->withErrors($validator);
        }

        // $file = $req->file('dp');
        // $imageName = time().'.'.$file->getClientOriginalExtension();
        // Storage::putFileAs('public/images', $file,$imageName);
        // $imageName = 'images/'.$imageName;


        $customer = new Customer();
        $customer->role = $req->role;
        $customer->name = $req->name;
        $customer->phone = $req->phoneNumber;
        $customer->email = $req->email;
       // $customer->display_picture_link = $imageName;
        $customer->password = bcrypt($req->password);
        $customer->status = 'ACTIVE';



        $customer->save();

        $this->sendEmail("registration",$req->email);

        return redirect('/login');
    }

    public function registerWithGoogle(){
        Session::put('registerAs','CUSTOMER');

        return redirect('/auth/google');
    }

    public function sendEmail($type, $email){
        Mail::to($email)->send(new Email(null,null,null,$type));
    }

}
