<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brokers extends Model
{
    use HasFactory;
    protected $table = 'tbl_brokers';

    protected $fillable = [
        'name','address','mobile_number','email','password','referral_code','is_approve','is_active','otp','otp_time','is_otp_verify','is_delete','created_at' ,'updated_at', 'header_image', 'stamp_image', 'website'
    ];

    public function userDetails()
    {
        return $this->hasOne(UserDetails::class,'user_id','id')->where('user_type','broker');
    }
}
