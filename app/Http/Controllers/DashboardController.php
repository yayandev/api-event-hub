<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'customer') {
            return $this->customerDashboard($user);
        } elseif ($user->role === 'organizer') {
            return $this->organizerDashboard($user);
        } elseif ($user->role === 'admin') {
            return $this->adminDashboard();
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }

    protected function customerDashboard(User $user)
    {
        $totalPembelian = Order::where('user_id', $user->id)->where('status', 'paid')->sum('total_amount');
        $totalTiket = Ticket::whereHas('order', function ($query) use ($user) {
            $query->where('user_id', $user->id)->where('status', 'paid');
        })->count();
        $totalEventAkanDatang = Order::where('user_id', $user->id)
            ->where('status', 'paid')
            ->whereHas('event', function ($query) {
                $query->where('start_datetime', '>', Carbon::now());
            })->count();

        return response()->json([
            'message' => 'success',
            'data' => [
                'total_pembelian' => $totalPembelian,
                'total_tiket' => $totalTiket,
                'total_event_akan_datang' => $totalEventAkanDatang,
            ]
        ]);
    }

    protected function organizerDashboard(User $user)
    {
        $events = Event::where('organizer_id', $user->id)->pluck('id');

        $totalEvent = $events->count();
        $totalTiketTerjual = TicketType::whereIn('event_id', $events)->sum('sold_quantity');
        $totalPendapatan = Order::whereIn('event_id', $events)->where('status', 'paid')->sum('total_amount');

        return response()->json([
            'message' => 'success',
            'data' => [
                'event_dibuat' => $totalEvent,
                'tiket_terjual' => $totalTiketTerjual,
                'total_pendapatan' => $totalPendapatan,
            ]
        ]);
    }

    protected function adminDashboard()
    {
        $totalUsers = User::count();
        $totalEventAktif = Event::where('status', 'published')->count();
        $totalOrganizer = User::where('role', 'organizer')->count();
        $totalTransaksi = Order::where('status', 'paid')->sum('total_amount');

        return response()->json([
            'message' => 'success',
            'data' => [
                'total_users' => $totalUsers,
                'total_event_aktif' => $totalEventAktif,
                'total_organizer' => $totalOrganizer,
                'total_transaksi' => $totalTransaksi,
            ]
        ]);
    }
}
