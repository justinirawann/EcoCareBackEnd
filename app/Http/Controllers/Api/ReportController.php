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
        $reports = Report::with(['user:id,name,email', 'assignedPetugas:id,name,email'])
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
            'admin_notes' => 'nullable|string',
            'fee_amount' => 'nullable|numeric|min:0'
        ]);

        $report = Report::findOrFail($id);
        $report->status = $request->status;
        if ($request->admin_notes) {
            $report->admin_notes = $request->admin_notes;
        }
        if ($request->fee_amount) {
            $report->fee_amount = $request->fee_amount;
        }
        $report->save();

        return response()->json([
            'status' => true,
            'message' => 'Status laporan berhasil diupdate',
            'data' => $report
        ]);
    }

    public function assignPetugas(Request $request, $id)
    {
        $request->validate([
            'petugas_id' => 'required|exists:users,id'
        ]);

        $report = Report::findOrFail($id);
        $report->assigned_petugas_id = $request->petugas_id;
        $report->save();

        return response()->json([
            'status' => true,
            'message' => 'Petugas berhasil ditugaskan',
            'data' => $report->load(['user', 'assignedPetugas'])
        ]);
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:paid,unpaid'
        ]);

        $report = Report::findOrFail($id);
        
        if ($report->status === 'completed') {
            return response()->json([
                'status' => false,
                'message' => 'Laporan sudah selesai, tidak dapat mengubah status pembayaran'
            ], 400);
        }

        $report->payment_status = $request->payment_status;
        $report->save();

        return response()->json([
            'status' => true,
            'message' => 'Status pembayaran berhasil diupdate',
            'data' => $report
        ]);
    }

    public function updateFee(Request $request, $id)
    {
        $request->validate([
            'fee_amount' => 'required|numeric|min:0'
        ]);

        $report = Report::findOrFail($id);
        
        if ($report->status === 'completed') {
            return response()->json([
                'status' => false,
                'message' => 'Laporan sudah selesai, tidak dapat mengubah biaya'
            ], 400);
        }

        $report->fee_amount = $request->fee_amount;
        $report->save();

        return response()->json([
            'status' => true,
            'message' => 'Biaya berhasil diupdate',
            'data' => $report
        ]);
    }

    public function completeReport(Request $request, $id)
    {
        $report = Report::where('id', $id)
            ->where('assigned_petugas_id', $request->user()->id)
            ->firstOrFail();

        // Validasi bahwa biaya sudah diset dan sudah dibayar
        if (!$report->fee_amount || $report->fee_amount <= 0) {
            return response()->json([
                'status' => false,
                'message' => 'Biaya belum ditetapkan. Set biaya terlebih dahulu.'
            ], 400);
        }

        if ($report->payment_status !== 'paid') {
            return response()->json([
                'status' => false,
                'message' => 'Pembayaran belum lunas. Pastikan user sudah membayar.'
            ], 400);
        }

        $report->status = 'completed';
        $report->save();

        return response()->json([
            'status' => true,
            'message' => 'Laporan berhasil diselesaikan',
            'data' => $report
        ]);
    }

    public function petugasReports(Request $request)
    {
        $reports = Report::where('assigned_petugas_id', $request->user()->id)
            ->with(['user:id,name,email,phone,address'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $reports
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
