<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class QRcode extends Model{

    use HasFactory;
    
    protected $table = 'qr_codes';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class, 'user_id');
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'user_id');
    }

    public function getUser(): Attribute{   
        return new Attribute(
            get:function(){ 
                if ($this->user_type == 'USER'){
                    $user = $this->user;
                }
                elseif($this->user_type == 'AGENT'){
                    $user = $this->agent;
                }
                elseif($this->user_type == 'MERCHANT'){ 
                    $user = $this->merchant;
                }

                return @$user;
            },
        );
    }

}
