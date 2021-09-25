<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDetails extends Model
{
    use HasFactory;
     protected $table = 'tbl_user_details';

    public function country()
    {
        return $this->hasOne(Country::class,'id','country_id');
    }

    public function state()
    {
        return $this->hasOne(State::class,'id','state_id');
    }

    public function city()
    {
        return $this->hasOne(City::class,'id','city_id');
    }

    public function station()
    {
        return $this->hasOne(Station::class,'id','station_id');
    }

    public function buyer()
    {
        return $this->hasOne(Buyers::class,'id','user_id');
    }

    public function seller()
    {
        return $this->hasOne(Sellers::class,'id','user_id');
    }
}
