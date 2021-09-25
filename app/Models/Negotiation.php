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
    public function logs()
    {
        return $this->hasMany(NegotiationLog::class,'negotiation_id','id');
    }
}
