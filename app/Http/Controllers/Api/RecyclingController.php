<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RecyclingOrder;
use Illuminate\Http\Request;

class RecyclingController extends Controller
{
    public function create(Request $request)
    {
        // Cek apakah user memiliki role 'user'
        if (!$request->user()->hasRole('user')) {
            return response()->json([
                'status' => false,
                'message' => 'Akses ditolak. Hanya user yang bisa membuat pesanan daur ulang.'
            ], 403);
        }

        $request->validate([
            'category' => 'required|in:Logam,Minyak,Kertas,Elektronik,Besi,Kaca,Plastik',
            'weight' => 'required|numeric|min:0.1',
            'description' => 'nullable|string',
            'pickup_address' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('recycling-images', 'public');
        }

        $order = RecyclingOrder::create([
            'user_id' => $request->user()->id,
            'category' => $request->category,
            'weight' => $request->weight,
            'description' => $request->description,
            'image' => $imagePath,
            'pickup_address' => $request->pickup_address,
            'status' => 'Pending'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Pesanan daur ulang berhasil dibuat',
            'order' => $order
        ]);
    }

    public function myOrders(Request $request)
    {
        // Cek apakah user memiliki role 'user'
        if (!$request->user()->hasRole('user')) {
            return response()->json([
                'status' => false,
                'message' => 'Akses ditolak. Hanya user yang bisa melihat pesanan daur ulang.'
            ], 403);
        }

        $orders = RecyclingOrder::where('user_id', $request->user()->id)
            ->with('petugas')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'orders' => $orders
        ]);
    }

    // Admin methods
    public function adminIndex()
    {
        $orders = RecyclingOrder::with(['user', 'petugas'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'orders' => $orders
        ]);
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'price_per_kg' => 'required|numeric|min:0',
            'admin_notes' => 'nullable|string'
        ]);

        $order = RecyclingOrder::findOrFail($id);
        $totalPrice = $request->price_per_kg * $order->weight;

        $order->update([
            'price_per_kg' => $request->price_per_kg,
            'total_price' => $totalPrice,
            'admin_notes' => $request->admin_notes,
            'status' => 'Approved'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Pesanan berhasil disetujui',
            'order' => $order
        ]);
    }

    public function reject(Request $request, $id)
    {
        $order = RecyclingOrder::findOrFail($id);
        
        $order->update([
            'admin_notes' => $request->admin_notes,
            'status' => 'Ditolak'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Pesanan ditolak'
        ]);
    }

    public function assignPetugas(Request $request, $id)
    {
        $request->validate([
            'petugas_id' => 'required|exists:users,id'
        ]);

        $order = RecyclingOrder::findOrFail($id);
        
        $order->update([
            'petugas_id' => $request->petugas_id,
            'status' => 'Berjalan'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Petugas berhasil ditugaskan'
        ]);
    }

    // Petugas methods
    public function petugasTasks(Request $request)
    {
        $tasks = RecyclingOrder::where('petugas_id', $request->user()->id)
            ->with('user')
            ->whereIn('status', ['Berjalan', 'Selesai'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'tasks' => $tasks
        ]);
    }

    public function completeTask(Request $request, $id)
    {
        $order = RecyclingOrder::where('id', $id)
            ->where('petugas_id', $request->user()->id)
            ->where('status', 'Berjalan')
            ->firstOrFail();
        
        $order->update(['status' => 'Selesai']);

        return response()->json([
            'status' => true,
            'message' => 'Tugas berhasil diselesaikan'
        ]);
    }

    public function updatePaymentStatus(Request $request, $id)
    {
        $request->validate([
            'payment_status' => 'required|in:paid,unpaid'
        ]);

        $order = RecyclingOrder::findOrFail($id);
        $order->payment_status = $request->payment_status;
        $order->save();

        return response()->json([
            'status' => true,
            'message' => 'Status pembayaran berhasil diupdate',
            'order' => $order
        ]);
    }
}