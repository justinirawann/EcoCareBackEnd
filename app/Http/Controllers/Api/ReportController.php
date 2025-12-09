<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Report;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $reports
        ]);
    }

    public function verify(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:verified,completed,pending,rejected',
            'admin_notes' => 'nullable|string'
        ]);

        $report = Report::findOrFail($id);
        $report->status = $request->status;
        if ($request->admin_notes) {
            $report->admin_notes = $request->admin_notes;
        }
        $report->save();

        return response()->json([
            'status' => true,
            'message' => 'Status laporan berhasil diupdate',
            'data' => $report
        ]);
    }

    public function myReports(Request $request)
    {
        $reports = Report::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $reports
        ]);
    }

    public function update(Request $request, $id)
    {
        $report = Report::findOrFail($id);

        // Pastikan user hanya bisa edit laporan sendiri
        if ($report->user_id !== $request->user()->id) {
            return response()->json([
                'status' => false,
                'message' => 'Anda tidak punya akses untuk edit laporan ini'
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string',
            'photo' => 'nullable|image|max:2048'
        ]);

        $report->title = $request->title;
        $report->description = $request->description;
        $report->location = $request->location;
        $report->status = 'pending'; // Reset status ke pending setelah edit
        $report->admin_notes = null; // Hapus catatan admin lama

        // Upload foto baru jika ada
        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($report->photo) {
                Storage::disk('public')->delete($report->photo);
            }
            $report->photo = $request->file('photo')->store('reports', 'public');
        }

        $report->save();

        return response()->json([
            'status' => true,
            'message' => 'Laporan berhasil diupdate',
            'data' => $report
        ]);
    }

    public function create(Request $request)
    {
        // CEK PERMISSION → create_report
        if (! $request->user()->hasPermission('create_report')) {
            return response()->json([
                'status' => false,
                'message' => 'Anda tidak punya izin membuat laporan.'
            ], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string',
            'photo' => 'nullable|image|max:2048'
        ]);

        // Upload foto jika ada
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('reports', 'public');
        }

        $report = Report::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'photo' => $photoPath,
            'status' => 'pending'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Laporan berhasil dibuat',
            'data' => $report
        ]);
    }
}
