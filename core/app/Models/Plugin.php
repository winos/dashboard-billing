<?php

namespace App\Models;

use App\Traits\GlobalStatus;
use Illuminate\Database\Eloquent\Model;

class Plugin extends Model
{
    use GlobalStatus;

    protected $casts = ['meta_data'=>'object'];
}
