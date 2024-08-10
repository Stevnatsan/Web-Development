<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Validation\Rule;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function insertProduct(Request $req){

        $rules = [
            'name' => 'required|max:50|regex:/^[\pL\s\-]+$/u',
            'price' => 'required',
            'category' => 'required', Rule::in(['Main Course', 'Appetizer', 'Desserts']),
            'dp' => 'required|image',
            'desc' => 'required'
        ];

        $validator = Validator::make($req->all(), $rules);
        // $validator = $this->validate($req, $rules);

        if($validator->fails()){
            return back()->withErrors($validator);
        }

        $file = $req->file('dp');
        $imageName = time().'.'.$file->getClientOriginalExtension();
        Storage::putFileAs('public/images', $file,$imageName);
        $imageName = 'images/'.$imageName;

        $product = new Product();
        $product->name = $req->name;
        $product->price = $req->price;

        $product->description = $req->desc;
        $product->image = $imageName;
        // dd($req->category);
        $category = Category::where('name', $req->category)->first();
        $product->category_id = $category->id;

        $product->vendor_id = Auth::guard('webvendor')->user()->id;


        $product->save();

        return redirect('/product/vendor');
    }

    public function addIndex(){
        return view('addProduct');
    }

    public function editIndex(Product $id){
        return view('editProduct', ['product'=> $id]);

    }

    public function editProduct(Product $id, Request $req){

        $rules = [
            'name' => 'required|max:50|regex:/^[\pL\s\-]+$/u',
            'price' => 'required',
            'category' => 'required', Rule::in(['Main Course', 'Appetizer', 'Desserts']),
            'dp' => 'image',
            'desc' => 'required'
        ];

        $validator = Validator::make($req->all(), $rules);
        // $validator = $this->validate($req, $rules);

        if($validator->fails()){
            return back()->withErrors($validator);
        }

        if($req->hasFile('dp')){
            $file = $req->file('dp');
            $imageName = time().'.'.$file->getClientOriginalExtension();
            Storage::putFileAs('public/images', $file,$imageName);
            $imageName = 'images/'.$imageName;
        }
        else{
            $imageName = $id->image;
        }

        $id->name = $req->name;
        $id->price = $req->price;

        $id->description = $req->desc;
        $id->image = $imageName;
        // dd($req->category);
        $category = Category::where('name', $req->category)->first();
        $id->category_id = $category->id;

        $id->save();

        return redirect('/product/vendor');
    }

     public function search(Vendor $v, Request $request)
     {
        $vc = new VendorController();
        $error = $vc->checkInAnotherVendorPage($v->id);
         return view('productList',[
            'vendor' => $v,
             'products' => Product::where('name', 'LIKE', "%$request->search%")->where('vendor_id','like',"$v->id")->paginate(3),
             'error' => $error
         ]);
     }

     public function removeProduct(Product $p){
        $p->delete();
        return redirect('product/vendor')->with('message','Product successfuly removed!');
     }
}
