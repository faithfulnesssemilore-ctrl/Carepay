<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class Controller implements HasMiddleware
{
    // Roles constants
    const ROLE_ADMIN = 'admin';

    const ROLE_USER = 'user';

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            'auth',
        ];
    }
}
