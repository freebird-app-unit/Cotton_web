<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sellers extends Model
{
    use HasFactory;
    protected $table = 'tbl_sellers';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name','address','mobile_number','email','password','referral_code','is_approve','is_active','otp','otp_time','is_otp_verify','is_delete','created_at' ,'updated_at'
    ];

}
