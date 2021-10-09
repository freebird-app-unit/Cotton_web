<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NegotiationComplete extends Model
{
    use HasFactory;
    protected $table = 'tbl_negotiation_complete';

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
    public function post()
    {
        return $this->hasOne(Post::class,'id','post_notification_id');
    }
    public function notification()
    {
        return $this->hasOne(Notification::class,'id','post_notification_id');
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
    public function deal_pdf()
    {
        return $this->hasOne(DealPdf::class,'deal_id','id');
    }
    public function debit_note()
    {
        return $this->hasMany(NegotiationDebitNote::class,'negotiation_complete_id','id');
    }
}
