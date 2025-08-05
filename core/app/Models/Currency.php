<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model{

    use HasFactory;

    public function showStatusBadge(): Attribute{
        return new Attribute(
            get:function(){
                $html = '';

                if($this->status == 1){
                    $html = '<span class="badge badge--success">'.trans('Enabled').'</span>';
                }
                else{
                    $html = '<span class="badge badge--warning">'.trans('Disabled').'</span>';
                }
              
                return $html;
            }
        );
    }

    public function showDefaultBadge(): Attribute{
        return new Attribute(
            get:function(){
                $html = '';
        
                if($this->is_default == 1){
                    $html = '<span class="badge badge--success">'.trans('Default').'</span>';
                }
                else{
                    $html = '<span class="badge badge--warning">'.trans('Not Default').'</span>';
                }
                
                return $html;
            }
        );
    }

    public function showCurrencyType(): Attribute{
        return new Attribute(
            get:function(){
                $html = '';
        
                if($this->currency_type == 1){
                    $html = '<span class="text--primary">'.trans('Fiat Currency').'</span>';
                }
                else{
                    $html = '<span class="text--warning">'.trans('Crypto Currency').'</span>';
                }
                
                return $html;
            },
        );
    }

    public function scopeDefault($query){
        return $query->where('is_default', 1);
    }

    public function scopeEnable($query){
        return $query->where('status', 1);
    }

}
