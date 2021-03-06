<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buyers extends Model
{
    use HasFactory;
    protected $table = 'tbl_buyers';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name','address','mobile_number','email','password','referral_code','is_approve','is_active','otp','otp_time','is_otp_verify','is_delete','created_at' ,'updated_at'
    ];

    public function bank_details()
    {
        return $this->hasOne(BankDetails::class,'user_id','id');
    }

    public function user_details()
    {
        return $this->hasOne(UserDetails::class,'user_id','id')->where('user_type','buyer');
    }

    public function user_plan() {
        return $this->hasOne(UserPlan::class,'user_id','id')->where('user_type','buyer')->orderBy('id', 'DESC');
    }

    public function broker() {
        return $this->hasOne(AddBrokers::class,'buyer_id','id')->where(['user_type' => 'buyer', 'broker_type' => 'default'])->orderBy('id', 'DESC');
    }
}
