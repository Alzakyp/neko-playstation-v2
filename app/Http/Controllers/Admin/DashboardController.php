<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Playstation;
use App\Models\Reservation;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $data = [
            'totalPlaystations' => Playstation::count(),
            'availablePlaystationCount' => Playstation::where('status', 'available')->count(),
            'totalGames' => Game::count(),
            'totalReservations' => Reservation::count(),
            'totalRevenue' => Payment::where('payment_status', 'paid')->sum('amount'),
            'pendingReservations' => Reservation::where('status', 'pending')->count(),
            'recentReservations' => Reservation::with(['user', 'playstation'])
                ->latest()
                ->take(5)
                ->get(),
            'popularPlaystations' => Playstation::withCount('reservations')
                ->orderBy('reservations_count', 'desc')
                ->take(5)
                ->get(),
            'monthlyRevenue' => $this->getMonthlyRevenue()
        ];

        return view('admin.dashboard', compact('data'));
    }

    private function getMonthlyRevenue()
    {
        return Payment::select(
            DB::raw('SUM(amount) as total'),
            DB::raw("DATE_FORMAT(created_at, '%M') as month"),
            DB::raw('MONTH(created_at) as month_num')
        )
            ->where('payment_status', 'paid')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month', 'month_num')
            ->orderBy('month_num')
            ->get()
            ->pluck('total', 'month')
            ->toArray();
    }
}
