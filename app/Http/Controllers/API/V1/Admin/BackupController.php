<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class BackupController extends Controller
{
    protected $backupDir = 'backups';

    public function index()
    {
        if (!Storage::exists($this->backupDir)) {
            Storage::makeDirectory($this->backupDir);
        }

        $files = Storage::files($this->backupDir);
        $backups = [];

        foreach ($files as $file) {
            $backups[] = [
                'id' => base64_encode($file),
                'name' => basename($file),
                'size' => $this->formatBytes(Storage::size($file)),
                'date' => Carbon::createFromTimestamp(Storage::lastModified($file))->toDateTimeString(),
                'type' => str_contains($file, 'manual') ? 'Manual' : 'System'
            ];
        }

        // Sort by date descending
        usort($backups, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return response()->json($backups);
    }

    public function create()
    {
        if (!Storage::exists($this->backupDir)) {
            Storage::makeDirectory($this->backupDir);
        }

        $filename = 'manual_backup_' . date('Y-m-d_H-i-s') . '.sql';
        $path = storage_path('app/' . $this->backupDir . '/' . $filename);

        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');

        // Check if mysqldump is available
        // For Windows/Laragon, we might need full path if not in system PATH
        // But usually exec() uses the shell environment
        $command = "mysqldump --user={$dbUser} --password=\"{$dbPass}\" --host={$dbHost} {$dbName} > \"{$path}\"";
        
        // On Windows, sometimes we need to use 'start /B' or similar, but for now let's try direct
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            return response()->json([
                'message' => 'Backup failed. Ensure mysqldump is installed and accessible.',
                'error' => implode("\n", $output)
            ], 500);
        }

        return response()->json([
            'message' => 'Backup created successfully',
            'name' => $filename
        ]);
    }

    public function download($id)
    {
        $path = base64_decode($id);

        if (!Storage::exists($path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return Storage::download($path);
    }

    public function destroy($id)
    {
        $path = base64_decode($id);

        if (!Storage::exists($path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        Storage::delete($path);

        return response()->json(['message' => 'Backup deleted successfully']);
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
