<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Logout{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next){

        if(gs()->detect_activity){
            $adminGuard = auth()->guard('admin')->user(); 
    
            if(!$adminGuard){
                config(['session.lifetime'=> 5]);
            }
        }


        return $next($request);
    }

}
