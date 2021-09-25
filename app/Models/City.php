<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;
    protected $table = 'tbl_city';
    protected $primaryKey = 'id';

    protected $fillable = [
        'state_id','name','is_delete','created_at' ,'updated_at'
    ];

    public function state()
    {
        return $this->hasOne(State::class,'id','state_id');
    }
}
