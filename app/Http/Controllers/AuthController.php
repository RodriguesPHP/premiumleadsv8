<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index(){
        return view('auth.login');
    }

    public function login(Request $request){
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if(Auth::attempt($request->only('email','password'))){
            return redirect('/');
        }else{
            return redirect()->back()->with('error','Usuario ou senha incorreta!');
        }
    }

    public function logout(){
        Auth::logout();
        return redirect()->route('login');
    }
}
