<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AiToolController;
use App\Mcp\Servers\FileSystemServer;   
use Laravel\Mcp\Facades\Mcp;

Mcp::local('filesystem', FileSystemServer::class);
Route::post('/execute-tool', [AiToolController::class, 'execute']);