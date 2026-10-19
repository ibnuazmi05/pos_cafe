<?php

namespace App\Http\Middleware;

use App\Models\Shift;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveShift
{
    public function handle(Request $request, Closure $next): Response
    {
        $shift = Shift::open()->where('user_id', $request->user()->id)->latest('opened_at')->first();

        if (! $shift) {
            $message = 'Buka shift terlebih dahulu sebelum melakukan transaksi.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 409);
            }

            return redirect()->route('kasir.shift.index')->with('error', $message);
        }

        $request->attributes->set('shift', $shift);

        return $next($request);
    }
}
