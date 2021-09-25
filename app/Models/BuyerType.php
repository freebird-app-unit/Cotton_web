<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BuyerType extends Model
{
    use HasFactory;
    protected $table = 'tbl_buyer_type';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name','is_active','is_delete','created_at' ,'updated_at'
    ];
}
