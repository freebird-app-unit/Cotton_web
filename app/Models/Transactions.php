<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    use HasFactory;
    protected $table = 'tbl_transactions';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id','user_type','type','amount','message','created_at' ,'updated_at'
    ];
}
