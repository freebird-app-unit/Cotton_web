<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
     protected $table = 'tbl_notification';

     public function product()
     {
         return $this->hasOne(Product::class,'id','product_id');
     }

     public function notification_details()
    {
        return $this->hasMany(NotificatonDetails::class,'notification_id','id');
    }
}
