<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransmitCondition extends Model
{
    use HasFactory;
    protected $table = 'tbl_transmit_condition';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name','is_dispatch','is_delete','created_at' ,'updated_at'
    ];
}
