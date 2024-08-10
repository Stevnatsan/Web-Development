<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VendorProfileController extends Controller
{
    public function viewVendorProfile(){
        $v = Vendor::where('id',Auth::guard('webvendor')->user()->id)->first();
        $editmode = false;
        $editprofpic = false;
        $membership = json_decode($v->vendor_membership);
        if($membership != null && $membership->status == 'ACTIVE') $ismember = true;
        else $ismember = false;
        return view('vendorprofile',[
            'user' => $v,
            'editMode' => $editmode,
            'editprofpic' => $editprofpic,
            'membership' => $membership,
            'isMember' => $ismember
        ]);
    }

    public function enableEdit(){
        $v = Vendor::where('id',Auth::guard('webvendor')->user()->id)->first();
        $editmode = true;
        $editprofpic = false;
        $membership = json_decode($v->vendor_membership);
        if($membership != null && $membership->status == 'ACTIVE') $ismember = true;
        else $ismember = false;
        return view('vendorprofile',[
            'user' => $v,
            'editMode' => $editmode,
            'editprofpic' => $editprofpic,
            'membership' => $membership,
            'isMember' => $ismember
        ]);
    }

    public function showEditPict(){
        $v = Vendor::where('id',Auth::guard('webvendor')->user()->id)->first();
        $editmode = true;
        $editprofpic = true;
        $membership = json_decode($v->vendor_membership);
        if($membership != null && $membership->status == 'ACTIVE') $ismember = true;
        else $ismember = false;
        return view('vendorprofile',[
            'user' => $v,
            'editMode' => $editmode,
            'editprofpic' => $editprofpic,
            'membership' => $membership,
            'isMember' => $ismember
        ]);
    }

    public function editProfile(Request $request)
    {
        $v = Vendor::where('id',Auth::guard('webvendor')->user()->id)->first();
        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'description' => 'required',
            'password' => 'nullable'
        ]);

        if(empty($validated['password'])){
            unset($validated['password']);
        }

        DB::table('vendors')->where('id', $v->id)->update($validated);
        return redirect('vendor/profile')->with('message','Profile edited successfully');
    }

    public function editPicture(Request $request)
    {
        $v = Vendor::where('id',Auth::guard('webvendor')->user()->id)->first();
        if($request->hasFile('image')){
            $fileImage = $request->file('image');
            $imageName ='user '.$v->name.'.'.$fileImage->getClientOriginalExtension();
            Storage::putFileAs('public/images', $fileImage, $imageName);
            $imageName = 'images/'.$imageName;
        }
        else{
            $imageName = $v->image;
        }

        DB::table('vendors')->where('id', $v->id)->update([
            'image' => $imageName
        ]);

        return redirect('vendor/profile')->with('message','Profile picture edited succesfully');
    }

    public function removePicture()
    {
        $v = Vendor::where('id',Auth::guard('webvendor')->user()->id)->first();
        Storage::delete ('public/image/'.$v->image);
        DB::table('vendors')->where('id', $v->id)->update([
            'image' => NULL
        ]);

        return redirect('vendor/profile')->with('message','Profile picture removed!');
    }
}
