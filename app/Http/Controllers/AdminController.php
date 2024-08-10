<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\OrderDetail;
use App\Models\Customer;
use App\Mail\Email;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\CategoryReporting;

class AdminController extends Controller
{
    public function index(){
        $customers = Customer::paginate(5, ['*'], 'customers');
        $vendors = Vendor::paginate(5, ['*'], 'vendors');
        $topCategory = CategoryReporting::orderBy('product_sold','DESC')->first();
        $totalCustomer = Customer::get()->count();
        $totalVendor = Vendor::get()->count();

        return view('adminPage', ['customers'=> $customers , 'vendors' => $vendors , 'totalCustomer' => $totalCustomer, 'totalVendor' => $totalVendor, 'topCategory' => $topCategory]);
    }

    public function deActivateUser($id, Request $req){
        if($req->role == "CUSTOMER"){
            $customer = Customer::find($id);
            $customer->status = 'INACTIVE';
            $customer->status_updated_by = Auth::guard('webadmin')->user()->id;
            $customer->save();
            return redirect()->back();
        }
        else if($req->role == "VENDOR"){
            $vendor = Vendor::find($id);
            $vendor->status = 'INACTIVE';
            $vendor->status_updated_by = Auth::guard('webadmin')->user()->id;
            $vendor->save();
            return redirect()->back();
        }
    }

    public function activateUser($id, Request $req){
        if($req->role == "CUSTOMER"){
            $customer = Customer::find($id);
            $customer->status = 'ACTIVE';
            $customer->status_updated_by = Auth::guard('webadmin')->user()->id;
            $customer->save();
            return redirect()->back();
        }
        else if($req->role == "VENDOR"){
            $vendor = Vendor::find($id);
            $vendor->status = 'ACTIVE';
            $vendor->status_updated_by = Auth::guard('webadmin')->user()->id;
            $vendor->save();
            return redirect()->back();
        }
    }

    public function viewOrders(){
        return view('admin-payment',[
            'orders' => Order::all()
        ]);
    }

    public function viewTransaction(Order $o){
        return view('transaction',[
            'order' => $o,
            'vendor' => Vendor::where('id', $o->vendor_id)->first()
        ]);
    }

    public function acceptPaymentProof(Order $o){
        $o->status = 'ON GOING';
        $o->payment_proof_status = 'APPROVED';
        $o->save();
        $orderDetails = OrderDetail::where('order_id', $o->id)->get();
        $this->sendEmail($o,$orderDetails,$o->vendor_id,"checkout");
        $this->sendEmailVendor($o,$o->vendor_id,"order status updated");
        return redirect('admin-payment')->with('message','Payment proof successfully approved');
    }

    public function rejectPaymentProof(Order $o){
        $o->payment_proof_status = 'REJECTED';
        $o->save();
        return redirect('admin-payment')->with('message','Payment proof successfully rejected');
    }

    public function sendEmail($order,$orderDetails,$vendor_id,$type){
        $vendor = Vendor::find($vendor_id);
        $customer = Customer::find($order->customer_id);
        if(strcmp($type,"checkout")==0){
            Mail::to($customer->email)->send(new Email($order,$orderDetails,$vendor,$type));
        }
        else if(strcmp($type,"order status updated")==0){
            Mail::to($customer->email)->send(new Email($order,$orderDetails,$vendor,$type));
        }
     }

     public function sendEmailVendor($order,$vendor_id,$type){
        $vendor = Vendor::find($vendor_id);
        Mail::to($vendor->email)->send(new Email($order,null,$vendor,$type));
     }
}
