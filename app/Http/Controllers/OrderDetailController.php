<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Vendor;

class OrderDetailController extends Controller
{
    public function index(Order $id){
         $orderDetails = OrderDetail::where('order_id', $id->id)->get();
         $order = Order::where('id', $id->id)->first();
         $vendor = Vendor::find($id->vendor_id);
         if($order->nego_price){
            $nego_price = $order->nego_price;
         } else{
            $nego_price = NULL;
         }
        return view('orderDetail', [
            'orderDetails' => $orderDetails,
            'order' => $id,
            'vendor' => $vendor,
            'nego_price' => $nego_price
        ]);

    }
}
