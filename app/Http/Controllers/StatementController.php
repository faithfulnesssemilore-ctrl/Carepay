<?php

namespace App\Http\Controllers;

use App\Jobs\ExportStatementJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StatementController extends Controller
{
    public function export(Request $request)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        dispatch(new ExportStatementJob(
            $request->user()->id,
            $validated['start_date'],
            $validated['end_date']
        ));

        return response()->json([
            'message' => 'Statement generation started. Check your email when the file is ready!',
        ], 202);
    }

    public function download(Request $request, $file)
    {
        if (! preg_match('/^[A-Za-z0-9_-]+\.xlsx$/', $file)) {
            abort(400, 'Invalid file name');
        }

        $filePath = "statements/{$file}";

        if (! Storage::exists($filePath)) {
            abort(404, 'File not found');
        }

        $userId = $request->user()->id;
        if (! Str::startsWith($file, "{$userId}-")) {
            abort(403, 'Unauthorized');
        }

        return Storage::download($filePath);
    }
}
