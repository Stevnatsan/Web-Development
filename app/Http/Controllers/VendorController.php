<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Mail\Email;
use App\Models\ProductReporting;
use App\Models\VendorReporting;

class VendorController extends Controller
{

    public function index(){

        $vendors = Vendor::orderBy('rating','DESC')->paginate(2);
        return view('vendorList', ['vendors'=> $vendors]);
    }

    public function getFeaturedVendors(){
        $vendorAll = Vendor::where(function ($query){
            $query->whereNull('vendor_membership')
                  ->orWhereJsonContains('vendor_membership->status','INACTIVE');
        })->get();
        $vendors = Vendor::whereNotNull('vendor_membership')->whereJsonContains('vendor_membership->status','ACTIVE')->get();

        if($vendors->count() < 2){
            foreach($vendorAll as $vendor){
                $vendors->push($vendor);
                if($vendors->count() == 2){
                    break;
                }
            }
        }

        return $vendors;
    }

    public function getTopRatedVendor(){
        $vendor = Vendor::orderBy('rating', 'DESC')->limit(3)->get();
        return $vendor;
    }

    public function getTopVendor(){
        $vendorReporting = VendorReporting::orderBy('number_of_transaction', 'DESC')->first();
        // $vendor = Vendor::where('id',$vendorReporting->vendor_id)->first();
        return $vendorReporting;
    }

    public function getTopProduct(){
        $productReporting = ProductReporting::orderBy('product_sold', 'DESC')->first();
        // $product = Product::where('id',$productReporting->product_id)->first();
        return $productReporting;
    }

    public function indexHomepage(){
        $featuredVendors = $this->getFeaturedVendors();
        $topRatedVendors = $this->getTopRatedVendor();
        $topVendor = $this->getTopVendor();
        $topProduct = $this->getTopProduct();

        // $date = Carbon::now();
        //  dd($date->month);
        return view('homepage', ['featuredVendors'=> $featuredVendors , 'topRatedVendors' => $topRatedVendors,
         'topVendor'=> $topVendor, 'topProduct' => $topProduct]);

    }

    public function register(Request $req){

        $rules = [
            'name' => 'required|max:50|regex:/^[\pL\s\-]+$/u',
            'phoneNumber' => 'required|regex:/^(0)[0-9]{11}/',
            'email' => 'required|email:rfc,dns|unique:customers',
            'role' => 'required', Rule::in(['CUSTOMER', 'VENDOR']),
            'dp' => 'image',
            'password' => 'required | min:8 | alpha_num |confirmed'
        ];

        $validator = Validator::make($req->all(), $rules);
        // $validator = $this->validate($req, $rules);

        if($validator->fails()){
            return back()->withErrors($validator);
        }

        $vendor = new Vendor();
        if($req->hasFile('dp')){
            $file = $req->file('dp');
            $imageName = time().'.'.$file->getClientOriginalExtension();
            Storage::putFileAs('public/images', $file,$imageName);
            $imageName = 'images/'.$imageName;
            $vendor->image = $imageName;
        }

        $vendor->phone = $req->phoneNumber;
        $vendor->description ="";
        $vendor->rating = 3;
        $vendor->role = $req->role;
        $vendor->name = $req->name;
        $vendor->email = $req->email;
        $vendor->password = bcrypt($req->password);
        $vendor->status = 'ACTIVE';
        $vendor->save();

        $this->sendEmail('registration',$req->email);

        return redirect('/login');
    }

    public function sendEmail($type,$email){
        Mail::to($email)->send(new Email(null,null,null,$type));
     }

    public function checkInAnotherVendorPage($id){
        $carts = session()->get('cart');
        if(empty($carts)){
            return "-1";
        }
        else{
            $cart = reset($carts);
            if($id == $cart['vendor_id']){
                return "-1";
            }
            return "1";
        }
    }

    public function showProductList(Vendor $v){
        $products = Product::where('vendor_id', $v->id)->paginate(3);
        $error = $this->checkInAnotherVendorPage($v->id);
        // dd($error);
        return view('productList',[
            'vendor' => $v,
            'products' => $products,
            'error' => $error,
        ]);
    }

    public function showVendorProductList(){
        $v = Auth::guard('webvendor')->user();
        $products = Product::where('vendor_id',Auth::guard('webvendor')->user()->id)->paginate(3);
        $vendorReporting = VendorReporting::where('vendor_id', $v->id)->where('month',
        Carbon::now()->month)->first();
      
        $totalProductSold = ProductReporting::where('vendor_id', $v->id)->sum('product_sold');
        $productReporting = ProductReporting::where('vendor_id', $v->id)->orderBy('product_sold',
        'DESC')->first();
        return view('productList',[
            'products' => $products,
            'vendor' => Auth::guard('webvendor')->user(),
            'vendorReporting' => $vendorReporting,
            'productReporting' => $productReporting,
            'totalProductSold' => $totalProductSold
        ]);
    }

    public function search(Request $request)
    {
        // $vendorsPaginate = Vendor::paginate(3);
        return view('vendorList',[
            'vendors' => Vendor::where('name', 'LIKE', "%$request->search%")->paginate(2)
        ]);
    }

    public function registerWithGoogle(){
        Session::put('registerAs','VENDOR');

        return redirect('/auth/google');
    }

}
