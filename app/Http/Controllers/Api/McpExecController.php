<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request as HttpRequest;
// Import class MCP kamu
use Laravel\Mcp\Request as McpRequest; 
use App\Mcp\Tools\WriteFileTool;
use App\Mcp\Tools\ReadFileTool;
use App\Mcp\Tools\ListFilesTool;

class McpExecController extends Controller
{
    public function handle(HttpRequest $httpInput)
    {
        // 1. Validasi input dari n8n
        $httpInput->validate([
            'tool' => 'required|string',
            'arguments' => 'array', // n8n biasanya kirim dalam key 'arguments' atau 'params'
        ]);

        $toolName = $httpInput->input('tool');
        $args = $httpInput->input('arguments', []);

        // 2. Bungkus arguments ke dalam object Laravel\Mcp\Request
        // Asumsi: Constructor McpRequest menerima array arguments. 
        // Jika constructor berbeda, sesuaikan baris ini.
        $mcpRequest = new McpRequest($args); 

        try {
            // 3. Pilih Tool berdasarkan nama
            $tool = match ($toolName) {
                'write_file' => new WriteFileTool(),
                'read_file' => new ReadFileTool(),
                'list_files' => new ListFilesTool(),
                default => null,
            };

            if (!$tool) {
                return response()->json(['error' => "Tool '$toolName' tidak ditemukan."], 404);
            }

            // 4. Jalankan method handle() milik Tool
            $mcpResponse = $tool->handle($mcpRequest);

            // 5. Kembalikan result (Asumsi McpResponse punya method content() atau __toString())
            // Sesuaikan cara mengambil isi response sesuai library MCP kamu.
            // Biasanya ada method seperti result(), content(), atau property public.
            return response()->json([
                'status' => 'success',
                'data' => $mcpResponse
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }
}