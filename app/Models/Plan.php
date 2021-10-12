<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    protected $table = 'tbl_plans';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 'validity', 'price', 'created_at' ,'updated_at'
    ];
}
