<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageResearchController;

Route::post('/image-research', [ImageResearchController::class, 'analyze']);