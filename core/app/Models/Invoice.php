<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use App\Traits\ApiQuery;

class Invoice extends Model
{
    use HasFactory, ApiQuery;

    protected $appends = ['link']; 

    public function items()
    {
        return $this->hasMany(InvoiceItem::class,'invoice_id');
    }
    
    public function currency()
    {
        return $this->belongsTo(Currency::class,'currency_id');
    }

    public function showStatusBadge(): Attribute{
        return new Attribute( 
            get:function(){
                $html = '';
              
                if($this->status == 1){
                    $html = '<span class="badge badge--success">'.trans('Published').'</span>';
                }
                elseif($this->status == 2){
                    $html = '<span class="badge badge--danger">'.trans('Discarded').'</span>';
                }
                else{
                    $html = '<span class="badge badge--warning">'.trans('Not Published').'</span>';
                }
              
                return $html;
            }
        );
    }

    public function showPaymentStatusBadge(): Attribute{
        return new Attribute( 
            get:function(){
                $html = '';
              
                if($this->pay_status == 1){
                    $html = '<span class="badge badge--success">'.trans('Paid').'</span>';
                }
                else{
                    $html = '<span class="badge badge--warning">'.trans('Unpaid').'</span>';
                }
              
                return $html;
            }
        );
    }

    public function link(): Attribute{
        return new Attribute( 
            get:function(){
               return route('invoice.payment', encrypt($this->invoice_num));
            }
        );
    }

}
 