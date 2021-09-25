<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    use HasFactory;
    protected $table = 'tbl_product_attribute';
    protected $primaryKey = 'id';

    protected $fillable = [
        'product_id','label','created_at' ,'updated_at'
    ];
}
