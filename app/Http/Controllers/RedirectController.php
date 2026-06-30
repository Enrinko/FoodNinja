<?php

namespace App\Http\Controllers;

use App\Models\Link;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    /**
     * Resolve a short code, record the click, and redirect to the original URL.
     */
    public function __invoke(Request $request, string $shortCode): RedirectResponse
    {
        $link = Link::where('short_code', $shortCode)->firstOrFail();

        $link->clicks()->create([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referer' => $request->headers->get('referer'),
        ]);

        return redirect()->away($link->original_url);
    }
}
