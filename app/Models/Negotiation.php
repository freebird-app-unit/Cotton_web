<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Negotiation extends Model
{
    use HasFactory;
    protected $table = 'tbl_negotiation';

    public function seller()
    {
        return $this->hasOne(Sellers::class,'id','seller_id');
    }
    public function buyer()
    {
        return $this->hasOne(Buyers::class,'id','buyer_id');
    }
    public function broker()
    {
        return $this->hasOne(Brokers::class,'id','broker_id');
    }
    public function transmit_conditions()
    {
        return $this->hasOne(TransmitCondition::class,'id','transmit_condition');
    }
    public function payment_conditions()
    {
        return $this->hasOne(PaymentCondition::class,'id','payment_condition');
    }
    public function labs()
    {
        return $this->hasOne(Lab::class,'id','lab');
    }
    public function logs()
    {
        return $this->hasMany(NegotiationLog::class,'negotiation_id','id');
    }
}
