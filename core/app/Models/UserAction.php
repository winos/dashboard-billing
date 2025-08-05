<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAction extends Model
{
    use HasFactory;

    protected $casts = ['details'=>'object'];
    
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function agent()
    {
        return  $this->belongsTo(Agent::class,'user_id');
    }
    public function merchant()
    {
        return  $this->belongsTo(Merchant::class,'user_id');
    }

}
