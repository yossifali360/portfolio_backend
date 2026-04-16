<?php

namespace App\Support;

use Illuminate\Http\Request;

final class RequestLocale
{
    public static function preferred(Request $request): string
    {
        $lang = $request->getPreferredLanguage(['ar', 'en']);

        return $lang !== null && str_starts_with(strtolower($lang), 'ar') ? 'ar' : 'en';
    }
}
