<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AttendanceController extends Controller
{
    // Check-in
    public function checkIn(Request $request) {
        $user = $request->user();

        // Validasi: Cek apakah hari ini sudah check-in tapi belum check-out
        $activeSession = Attendance::where('user_id', $user->id)
            ->whereNull('check_out')
            ->first();

        if ($activeSession) {
            return response()->json(['message' => 'Anda masih status Check-in. Harap Check-out dulu.'], 400);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'check_in' => Carbon::now(),
            'status' => 'present'
        ]);

        return response()->json([
            'message' => 'Check-in berhasil',
            'data' => $attendance
        ], 201);
    }

    // Check-out
    public function checkOut(Request $request) {
        $user = $request->user();

        // Cari sesi terakhir yang belum ditutup
        $attendance = Attendance::where('user_id', $user->id)
            ->whereNull('check_out')
            ->latest()
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'Tidak ada sesi aktif. Silahkan Check-in terlebih dahulu.'], 400);
        }

        $attendance->update([
            'check_out' => Carbon::now()
        ]);

        return response()->json([
            'message' => 'Check-out berhasil',
            'data' => $attendance
        ]);
    }

    // History
    public function history(Request $request) {
        $history = Attendance::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $history]);
    }
}