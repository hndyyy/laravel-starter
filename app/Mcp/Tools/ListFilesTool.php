<?php

namespace App\Mcp\Tools;

use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log; // Kita butuh ini buat ngintip

class ListFilesTool extends Tool
{
    protected string $description = 'Melihat daftar file dalam folder.';

    public function handle(Request $request): Response
    {
        // 1. LOGGING (Biar kita tau n8n ngirim apaan sebenarnya)
        // Cek file storage/logs/laravel.log di folder project abang nanti
        Log::info('--- AI LIST FILES REQUEST ---');
        Log::info('Raw Input:', $request->all());

        try {
            // 2. AMBIL DATA DENGAN SANGAT HATI-HATI
            $allData = $request->all();
            
            // Cari 'path' dimanapun dia berada
            $pathInput = $allData['path'] 
                         ?? $allData['arguments']['path'] 
                         ?? $allData['query'] 
                         ?? '';

            // 3. NORMALISASI (KUNCI ANTI ERROR 500) 🔑
            // Kalau inputnya Array, kita ambil elemen pertamanya atau encode jadi string
            if (is_array($pathInput)) {
                $pathInput = reset($pathInput); // Ambil item pertama
            }
            
            // Paksa jadi string biar ltrim tidak error
            $cleanPath = is_string($pathInput) ? ltrim($pathInput, '/\\') : '';

            // 4. EKSKUSI
            $targetPath = $cleanPath ? base_path($cleanPath) : base_path();
            
            Log::info("Target Path: " . $targetPath);

            if (!File::exists($targetPath)) {
                return Response::text("Error: Folder tidak ditemukan di path: " . $cleanPath);
            }

            if (!File::isDirectory($targetPath)) {
                return Response::text("Error: '$cleanPath' adalah FILE. Gunakan read_file untuk membacanya.");
            }

            // 5. HASIL
            $files = collect(File::files($targetPath))->map(fn($f) => '[FILE] ' . $f->getFilename());
            $dirs  = collect(File::directories($targetPath))->map(fn($d) => '[DIR]  ' . basename($d));

            $output = "Isi Folder ($cleanPath):\n" . $dirs->merge($files)->implode("\n");
            
            return Response::text($output);

        } catch (\Throwable $e) {
            // TANGKAP ERROR APAPUN
            Log::error("ListFiles Crash: " . $e->getMessage());
            return Response::text("SYSTEM ERROR (Gak Crash 500): " . $e->getMessage());
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'path' => $schema->string('Folder relative. Contoh: app/Http'),
        ];
    }
}   