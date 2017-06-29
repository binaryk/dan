<?php namespace App\Http\Middleware;

use Closure;
use App;
use Redirect;

class SSL
{

    public function handle($request, Closure $next)
    {

        if (App::environment('local')) {
            return Redirect::secure($request->path());
        }

        return $next($request);
    }
}