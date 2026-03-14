<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request as HttpRequest;
use Laravel\Mcp\Request as McpRequest;
use App\Mcp\Tools\ListFilesTool;
use App\Mcp\Tools\ReadFileTool;
use App\Mcp\Tools\WriteFileTool;
use ReflectionClass;

class AiToolController extends Controller
{
    public function execute(HttpRequest $httpReq)
    {
        // 1. Validasi Input: Butuh nama tool & argumennya
        $httpReq->validate([
            'tool' => 'required|string',
            'arguments' => 'array', 
        ]);

        $toolName = $httpReq->input('tool');
        $args = $httpReq->input('arguments', []);

        // 2. Bungkus Request (Logic sukses sebelumnya)
        $mcpReq = new class($args) extends McpRequest {
            private $data;
            public function __construct($data) { $this->data = $data; }
            public function get(string $key, mixed $default = null): mixed { return $this->data[$key] ?? $default; }
            public function all(mixed $keys = null): array { return $this->data; }
        };

        try {
            // 3. Routing Tool
            $tool = match ($toolName) {
                'list_files' => new ListFilesTool(),
                'read_file'  => new ReadFileTool(),
                'write_file' => new WriteFileTool(),
                default => null,
            };

            if (!$tool) {
                return response()->json(['status' => 'error', 'message' => "Tool '$toolName' tidak ditemukan."], 404);
            }

            // 4. Eksekusi
            $result = $tool->handle($mcpReq);

            // 5. Ekstraksi Data (Logic Reflection sukses sebelumnya)
            $finalOutput = $this->extractContent($result);

            return response()->json([
                'status' => 'success',
                'output' => $finalOutput
            ]);

        } catch (\Throwable $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // Helper untuk bongkar object protected
    private function extractContent($result)
    {
        if (!is_object($result)) return (string) $result;

        $reflector = new ReflectionClass($result);
        if (!$reflector->hasProperty('content')) return json_encode($result);

        $prop = $reflector->getProperty('content');
        $prop->setAccessible(true);
        $contentObj = $prop->getValue($result);

        if (is_object($contentObj)) {
            $contentReflector = new ReflectionClass($contentObj);
            if ($contentReflector->hasProperty('text')) {
                $textProp = $contentReflector->getProperty('text');
                $textProp->setAccessible(true);
                return $textProp->getValue($contentObj);
            }
        }
        return (string) $contentObj;
    }
}