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
}
