<?php 

if (!function_exists('route_exists')) {
    function route_exists($name) {
        return \Illuminate\Support\Facades\Route::has($name);
    }
}