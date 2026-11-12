<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Uso en rutas: ->middleware('rol:ADMINISTRADOR,RECEPCIONISTA')
class RolMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $usuario = $request->user();

        if (! $usuario || ! in_array($usuario->rol->nombre, $roles, true)) {
            abort(403, 'No tiene permiso para esta opción.');
        }

        return $next($request);
    }
}