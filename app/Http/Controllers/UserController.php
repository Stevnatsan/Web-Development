<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Mail\Email;
use Socialite;

class UserController extends Controller
{
    public function login(Request $request){
        $credential = [
            'email' => $request->email,
            'password' => $request->password
        ];
        // dd($request->role);

        if($request->remember_me) {
            Cookie::queue("email", $request->email);
            Cookie::queue("password", $request->password);

        }
        else {
            Cookie::queue(Cookie::forget("email"));
            Cookie::queue(Cookie::forget("password"));
        }
    
        switch($request->role) {
            case "CUSTOMER" :
                if(Auth::guard('webcustomer')->attempt($credential,true)){
                    $customer = Customer::where('id',Auth::guard('webcustomer')->user()->id)->first();
                    if($customer->status == 'INACTIVE'){
                        Auth::guard('webcustomer')->logout();
                        return redirect()->back()->withErrors('Your account is suspended !');
                    }
                    Session::put('mysession',Auth::guard('webcustomer')->user()->name);
                    return redirect('/');
                }
                else{
                    return redirect()->back()->withErrors('Username or Password is incorrect !');

                }
                break;
            case "VENDOR" :
                if(Auth::guard('webvendor')->attempt($credential,true)){
                    $vendor = Vendor::where('id',Auth::guard('webvendor')->user()->id)->first();
                    if($vendor->status == 'INACTIVE'){
                        Auth::guard('webvendor')->logout();
                        return redirect()->back()->withErrors('Your account is suspended !');
                    }
                    Session::put('mysession',Auth::guard('webvendor')->user()->name);
                    return redirect('/');
                }
                else{
                    return redirect()->back()->withErrors('Username or Password is incorrect !');

                }
                break;
            case "ADMIN" :
                if(Auth::guard('webadmin')->attempt($credential,true)){
                    Session::put('mysession',Auth::guard('webadmin')->user()->name);
                    return redirect('/manageUser');
                }
                else{
                    return redirect()->back()->withErrors('Username or Password is incorrect !');

                }
                break;
            default:
                return redirect()->back()->withErrors('Username or Password is incorrect !');    

        }


    }
    
    public function authGoogle() {
         return Socialite::driver('google')->redirect();
    }

    public function authGoogleCustomer(){
        Session::put('vendorRole',false);
        return Socialite::driver('google')->redirect();
    }

    public function authGoogleVendor(){
        Session::put('vendorRole',true);
        return Socialite::driver('google')->redirect();
    }



    public static function quickRandom($length = 16)
{
    $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
}

    public function sendEmail($type,$email){
        Mail::to($email)->send(new Email(null,null,null,$type));
    }

    public function googleCallback() {
        try {
            $user = Socialite::driver('google')->user();
            $customer = Customer::where('email', $user->email)->first();
            $vendor = Vendor::where('email', $user->email)->first();
            if($customer == null && session()->get('registerAs') == 'CUSTOMER') {
                $newCustomer = new Customer();
                $newCustomer->name = $user->name;
                $newCustomer->email = $user->email;
                $newCustomer->status = 'ACTIVE';
                $newCustomer->password = bcrypt('12343213123'); // temporary password
                $newCustomer->save();
                $this->sendEmail('registration',$user->email);
                $customer = Customer::where('email', $user->email)->first();
                Auth::guard('webcustomer')->login($customer);
                Session::put('mysession',Auth::guard('webcustomer')->user()->name);
                return redirect('/');
            }        
            else if($vendor == null && session()->get('registerAs') == 'VENDOR'){
                $newVendor = new Vendor();
                $newVendor->name = $user->name;
                $newVendor->email = $user->email;
                $newVendor->status = 'ACTIVE';
                $newVendor->password = bcrypt('asdasdsad'); // temporary password
                $newVendor->description = 'Add description in profile !'; // temporary description
                $newVendor->rating = 3; // temporary rating
                $newVendor->phone = 'Add phone number in profile !';
                $newVendor->save();
                $this->sendEmail('registration',$user->email);
                $vendor = Vendor::where('email', $user->email)->first();
                Auth::guard('webvendor')->login($vendor);
                Session::put('mysession',Auth::guard('webvendor')->user()->name);
                return redirect('/');
            }
                
            $isVendor = session()->get('vendorRole');

            if($isVendor) {
                if($vendor == null){
                    return redirect('/login')->withErrors('Your email doesn\'t exist!');

                }
                else{
                    if($vendor->status == 'INACTIVE'){
                        Auth::guard('webvendor')->logout();
                        return redirect('/login')->withErrors('Your account is suspended !');
                    }
                    Auth::guard('webvendor')->login($vendor);
                    Session::put('mysession',Auth::guard('webvendor')->user()->name);
                    return redirect('/');
                }
            }

            else if($customer != null ) {
                if($customer->status == 'INACTIVE'){
                    Auth::guard('webcustomer')->logout();
                    return redirect('/login')->withErrors('Your account is suspended !');
                } 
                Auth::guard('webcustomer')->login($customer);
                Session::put('mysession',Auth::guard('webcustomer')->user()->name);
                return redirect('/');
            }
            else{
                return redirect('/login')->withErrors('Your email doesn\'t exist!');
            }
          
        
        } catch (Exception $e) {
            return redirect('/login');
        }
    }

    public function logout(){
        if(Auth::guard('webcustomer')->check()){
            Auth::guard('webcustomer')->logout();
        }
        else if(Auth::guard('webvendor')->check()){
            Auth::guard('webvendor')->logout();
        }
        else if(Auth::guard('webadmin')->check()){
            Auth::guard('webadmin')->logout();
        }
        Session::flush();
        return redirect('/');
    }
}
