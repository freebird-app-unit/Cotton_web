<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;
    protected $table = 'tbl_news';
    protected $primaryKey = 'id';

    protected $fillable = [
        'name','image','description','is_delete','created_at' ,'updated_at'
    ];

}
