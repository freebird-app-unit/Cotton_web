<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddBrokers extends Model
{
    use HasFactory;
    protected $table = 'tbl_add_brokers';

	public function broker()
    {
        return $this->hasOne(Brokers::class,'id','broker_id');
    }

	public function seller()
    {
        return $this->hasMany(Sellers::class,'id','buyer_id');
    }
	public function buyer()
    {
        return $this->hasMany(Buyers::class,'id','buyer_id');
    }
}
