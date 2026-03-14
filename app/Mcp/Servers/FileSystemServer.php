<?php

namespace App\Mcp\Servers;

use Laravel\Mcp\Server; // <--- INI PERBAIKANNYA (File ada di src/Server.php)
use App\Mcp\Tools\ReadFileTool;
use App\Mcp\Tools\WriteFileTool;
use App\Mcp\Tools\ListFilesTool;
use App\Mcp\Tools\TriggerN8nTool;

class FileSystemServer extends Server
{
    public string $name = 'Laravel FileSystem';
    public string $version = '1.0.0';

    public array $tools = [
        ReadFileTool::class,
        WriteFileTool::class,
        ListFilesTool::class,
        TriggerN8nTool::class,
    ];
}