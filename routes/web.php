<?php

use App\Http\Controllers\CounterController;
use Illuminate\Support\Facades\Route;

Route::get('/', CounterController::class);
