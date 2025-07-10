<?php
use App\Http\Controllers\ImageResearchController;

Route::post('/image-research', [ImageResearchController::class, 'analyze']);