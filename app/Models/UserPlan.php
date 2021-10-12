<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPlan extends Model
{
    use HasFactory;
    protected $table = 'tbl_users_plan';
    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id','user_type', 'purchase_date', 'expiry_date', 'plan_id','status'
    ];

}
