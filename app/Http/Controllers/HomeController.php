<?php

namespace App\Http\Controllers;

use App\Models\Buyers;
use App\Models\Sellers;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $seller = Sellers::where('is_active',1)->where('is_delete',1)->get();
        $buyer = Buyers::where('is_active',1)->where('is_delete',1)->get();
    	return view('home',compact('seller','buyer'));
    }
}
