<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Hash;
use App\Models\Admin;
use App\Mail\AdminMail;

class AdminController extends Controller
{
    public function dashboard(){
        return view("admin.dashboard");
    }
    public function login(){
        return view("admin.login");
    }

    public function login_submit(Request $request){
        $request->validate([
            "email"=> "required|email",
            "password"=> "required",
        ]);

        $check = $request->all();
        $data = [
            'email' => $check['email'],
            'password'=> $check['password'],
        ];
    
        if(Auth::guard('admin')->attempt($data)){
            return redirect()->route("admin_dashboard");
        }else{
            return redirect()->back()->withErrors(["email" => "Username atau Password salah"]);
        }
    }

    public function logout(){
        Auth::guard("admin")->logout();
        return redirect()->route("admin_login")->with("success","Logged out successfully");
    }    
    public function forget_password(){
        return view("admin.forget_password");
    }

    public function forget_password_submit(Request $request)
    {
        $request->validate([
            "email" => "required|email",
            "password" => "required",
        ]);

        $credentials = [
            'email' => $request->email,
            'password' => $request->password,
        ];

        if (Auth::guard('admin')->attempt($credentials)) {
            return redirect()->route("admin_dashboard");
        } else {
            return redirect()->back()->withErrors(["email" => "Invalid Credentials"]);
        }
    }
}
