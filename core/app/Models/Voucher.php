<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ApiQuery;

class Voucher extends Model
{
    use HasFactory, ApiQuery;

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->where('user_type', 'USER');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    } 
    
    public function showUsedBadge(): Attribute{
        return new Attribute(
            get:function(){
                $html = '';

                if($this->is_used == 1){
                    $html = '<span class="badge badge--success">'.trans('Used').'</span>';
                }
                else{
                    $html = '<span class="badge badge--primary">'.trans('Not Used').'</span>';
                }
              
                return $html;
            }
        );
    }

    public function usedTime(){
        $time = trans('N/A');
        
        if ($this->is_used == 1){
            $time = showDateTime($this->updated_at);
        }
        
        return $time;
    }

}
