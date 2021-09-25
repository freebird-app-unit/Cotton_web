<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
	protected $data = array();
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function __construct(){
   		 $this->data['site_title'] = 'Cotton';
   		//  $this->data['site_title'] = (get_settings('site_name')!='')?get_settings('site_name'):'Cotton';
    }
}
