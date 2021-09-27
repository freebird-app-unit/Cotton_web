<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Post extends Model
{
    use HasFactory;
    protected $table = 'tbl_post';

    public function user_detail()
    {
        return $this->hasOne(UserDetails::class,'user_id','seller_buyer_id');
    }
    public function product()
    {
        return $this->hasOne(Product::class,'id','product_id');
    }
    public function seller()
    {
        return $this->hasOne(Sellers::class,'id','seller_buyer_id');
    }
    public function buyer()
    {
        return $this->hasOne(Buyers::class,'id','seller_buyer_id');
    }
}
