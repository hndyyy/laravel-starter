<?php

namespace App\Mcp\Tools;

use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Illuminate\Support\Facades\File;

class ReadFileTool extends Tool
{
    protected string $description = 'Membaca isi file code. Bisa membaca SATU file atau BANYAK file sekaligus (dipisah koma).';

    public function handle(Request $request): Response
    {
        // --- PERBAIKAN DI SINI (Ganti input() jadi get()) ---
        
        // 1. Coba ambil langsung dari key 'path'
        $rawInput = $request->get('path');

        // 2. Kalau kosong, coba cari di dalam 'arguments' (Logic Manual)
        if (empty($rawInput)) {
            $args = $request->get('arguments');
            // Cek apakah arguments itu array dan punya key 'path'
            if (is_array($args) && isset($args['path'])) {
                $rawInput = $args['path'];
            }
        }

        // 3. Kalau masih kosong, coba cari di 'query'
        if (empty($rawInput)) {
            $rawInput = $request->get('query');
        }
        
        // Default ke string kosong biar gak error
        if (is_null($rawInput)) {
            $rawInput = '';
        }

        // -----------------------------------------------------

        $paths = [];

        // Logic Parsing (Sama seperti sebelumnya)
        if (is_array($rawInput)) {
            $paths = $rawInput;
        } elseif (is_string($rawInput) && str_contains($rawInput, ',')) {
            $paths = explode(',', $rawInput);
        } else {
            $paths = [$rawInput];
        }

        $finalOutput = "";
        $filesReadCount = 0;

        foreach ($paths as $path) {
            $cleanPath = is_string($path) ? trim($path) : '';
            $cleanPath = ltrim($cleanPath, '/\\');
            
            if (empty($cleanPath)) continue;

            $fullPath = base_path($cleanPath);

            $finalOutput .= "========================================\n";
            $finalOutput .= "FILE: $cleanPath\n";
            $finalOutput .= "========================================\n";

            if (!File::exists($fullPath)) {
                $finalOutput .= "[ERROR] File tidak ditemukan.\n\n";
                continue;
            }

            if (File::isDirectory($fullPath)) {
                $finalOutput .= "[ERROR] Ini FOLDER. Gunakan list_files.\n\n";
                continue;
            }
            
            if (File::size($fullPath) > 50 * 1024) {
                $finalOutput .= "[ERROR] File terlalu besar (>50KB).\n\n";
                continue;
            }

            $content = File::get($fullPath);
            $finalOutput .= $content . "\n\n";
            $filesReadCount++;
        }

        if ($filesReadCount === 0 && count($paths) > 0) {
            // Debugging: Tampilkan raw input biar ketahuan isinya apa
            return Response::text("Gagal membaca file. Raw Input: " . json_encode($rawInput));
        }

        if (empty($finalOutput)) {
            return Response::text("Tidak ada path file yang diberikan.");
        }

        return Response::text($finalOutput);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'path' => $schema->string('Path relative (contoh: routes/web.php). Pisahkan koma jika banyak.'),
        ];
    }
}