<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Module
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $access)
    {   
        $permission = module($access);
        if($permission->status == 0){ 

            if ($request->is('api/*')) {
                return response()->json([
                    'remark'=>'not_found',
                    'status'=>'error',
                    'message'=>['error'=>['Not found']],
                ]); 
            }else{
                abort(404);
            }

        }
        return $next($request);
    }
}
