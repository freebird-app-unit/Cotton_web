<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BrokerRequest extends Model
{
    use HasFactory;
    protected $table = 'tbl_broker_requests';

    public function buyer()
    {
        return $this->hasOne(Buyers::class,'id','buyer_id');
    }
}
