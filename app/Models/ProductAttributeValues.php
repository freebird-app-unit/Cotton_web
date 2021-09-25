<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttributeValues extends Model
{
    use HasFactory;
    protected $table = 'tbl_product_attribute_values';
    protected $primaryKey = 'id';

    protected $fillable = [
        'product_attribute_id','value','created_at' ,'updated_at'
    ];
}
