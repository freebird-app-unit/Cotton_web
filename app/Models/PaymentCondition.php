<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentCondition extends Model
{
    use HasFactory;
    protected $table = 'tbl_payment_condition';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name','description','is_delete','created_at' ,'updated_at'
    ];

}
