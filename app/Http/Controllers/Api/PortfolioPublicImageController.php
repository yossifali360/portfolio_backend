<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves uploaded portfolio images through Laravel so they work when the web server
 * returns 403 for direct {@see /storage/...} URLs (symlink, nginx rules, etc.).
 */
class PortfolioPublicImageController extends Controller
{
    public function show(string $filename): StreamedResponse|\Illuminate\Http\Response
    {
        if ($filename === '' || str_contains($filename, '/') || str_contains($filename, '..')) {
            abort(404);
        }

        $path = 'portfolio/'.$filename;

        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path, null, [
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
