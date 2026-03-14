<?php

namespace App\Mcp\Tools;

use Illuminate\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use Illuminate\Support\Facades\File;

class WriteFileTool extends Tool
{
    protected string $description = 'Menulis atau membuat file baru dengan konten teks. Jika folder belum ada, akan dibuat otomatis.';

    public function handle(Request $request): Response
    {
        // 1. LOGIKA PENGAMBILAN INPUT (Flexible)
        // Kita ambil input utama
        $pathInput = $request->get('path');
        $contentInput = $request->get('content');

        // Jika kosong/null, kita coba cari "harta karun" di dalam 'arguments'
        // (n8n kadang membungkusnya di dalam sini)
        if (empty($pathInput) || $contentInput === null) {
            $args = $request->get('arguments');
            if (is_array($args)) {
                $pathInput = $args['path'] ?? $pathInput;
                $contentInput = $args['content'] ?? $contentInput;
            }
        }

        // 2. VALIDASI DASAR
        if (empty($pathInput)) {
            return Response::text("Error: Parameter 'path' wajib diisi.");
        }
        if ($contentInput === null) {
            return Response::text("Error: Parameter 'content' wajib diisi (boleh string kosong, tapi tidak boleh null).");
        }

        // 3. SECURITY CHECK (Penting!)
        // Mencegah AI mengedit file sensitif atau naik ke folder root sistem
        if (str_contains($pathInput, '..') || str_contains($pathInput, '.env')) {
            return Response::text("Security Warning: Akses ke path '$pathInput' ditolak demi keamanan.");
        }

        // 4. PERSIAPAN PATH
        $cleanPath = ltrim($pathInput, '/\\'); // Hapus slash di depan biar gak konflik sama base_path
        $fullPath = base_path($cleanPath);
        $directory = dirname($fullPath);

        // 5. BUAT FOLDER OTOMATIS
        // Jika AI mau simpan di "analisis/laporan.md" tapi folder "analisis" belum ada
        if (!File::isDirectory($directory)) {
            try {
                File::makeDirectory($directory, 0755, true);
            } catch (\Exception $e) {
                return Response::text("Gagal membuat folder: " . $e->getMessage());
            }
        }

        // 6. TULIS FILE
        try {
            File::put($fullPath, $contentInput);
            return Response::text("Berhasil menyimpan file ke: $cleanPath");
        } catch (\Exception $e) {
            return Response::text("Gagal menulis file: " . $e->getMessage());
        }
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'path' => $schema->string('Path file relative project (contoh: analisis/hasil_review.md)'),
            'content' => $schema->string('Isi konten teks/kode yang akan disimpan ke dalam file.'),
        ];
    }
}