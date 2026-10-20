<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanExportPdf
{
    /**
     * Handle an incoming request.
     * Only Admin Management and User can export PDF.
     * Admin Inventory is forbidden according to the RBAC matrix.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user || !$user->isActive() || !$user->canExportPdf()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Fitur unduh / ekspor PDF tidak tersedia untuk role Anda.',
                ], 403);
            }

            abort(403, 'Akses ditolak. Fitur unduh / ekspor PDF tidak tersedia untuk role Anda.');
        }

        return $next($request);
    }
}

