<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    /**
     * Store temporary uploaded files
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120', // 5MB max
        ]);

        try {
            $file = $request->file('file');
            
            // Generate unique filename
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
            
            // Store in temp folder
            $path = $file->storeAs('temp', $filename, 'public');

            return response()->json([
                'success' => true,
                'path' => '/storage/' . $path,
                'filename' => $filename,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete temporary file
     */
    public function destroy($filename)
    {
        try {
            $path = 'temp/' . $filename;
            
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                
                return response()->json([
                    'success' => true,
                    'message' => 'File deleted successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'File not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clean up old temporary files (should be run via scheduler)
     */
    public function cleanupTemp()
    {
        try {
            $files = Storage::disk('public')->files('temp');
            $deletedCount = 0;
            
            foreach ($files as $file) {
                // Delete files older than 24 hours
                if (Storage::disk('public')->lastModified($file) < now()->subHours(24)->timestamp) {
                    Storage::disk('public')->delete($file);
                    $deletedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Cleaned up {$deletedCount} temporary files"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cleanup failed: ' . $e->getMessage()
            ], 500);
        }
    }
}