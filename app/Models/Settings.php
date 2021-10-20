<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    use HasFactory;
    protected $table = 'tbl_settings';
    protected $primaryKey = 'id';

    protected $fillable = [
        'site_name','site_email','site_contact','site_address','site_logo','negotiation_count','bunch','created_at' ,'updated_at', 'company_commission', 'broker_commission'
    ];

}
