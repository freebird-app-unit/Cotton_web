<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;
    protected $table = 'tbl_country';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name','is_delete','created_at' ,'updated_at'
    ];
}
