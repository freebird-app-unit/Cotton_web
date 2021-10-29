<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    use HasFactory;
    protected $table = 'tbl_station';

    protected $fillable = [
        'city_id','name','is_delete','created_at' ,'updated_at'
    ];

    public function city()
    {
        return $this->hasOne(City::class,'id','city_id');
    }
}
