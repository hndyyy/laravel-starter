<?php

use App\Mcp\Servers\FileSystemServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::local('filesystem', FileSystemServer::class);