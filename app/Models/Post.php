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
}
