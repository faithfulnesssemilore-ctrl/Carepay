<?php

namespace App\Http\Controllers;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
abstract class Controller
{
    //roles    const ROLE_ADMIN = 'admin';
    //roles    const ROLE_USER = 'user';
    public function __construct()
    {
        // Apply authentication middleware to all routes in this controller
        $this->middleware('auth');
        // Apply role-based access control middleware to specific routes
        // $this->middleware('role:' . self::ROLE_ADMIN)->only(['adminOnlyMethod']);
        // $this->middleware('role:' . self::ROLE_USER)->only(['userOnlyMethod']);
        $this->middleware('role:admin')->only(['adminOnlyMethod']);
        $this->middleware('role:user')->only(['userOnlyMethod']);
        
    }
}
