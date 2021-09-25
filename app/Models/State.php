<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;
    protected $table = 'tbl_state';
    protected $primaryKey = 'id';

    protected $fillable = [
        'country_id','name','is_delete','created_at' ,'updated_at'
    ];

    public function country()
    {
        return $this->hasOne(Country::class,'id','country_id');
    }
}
