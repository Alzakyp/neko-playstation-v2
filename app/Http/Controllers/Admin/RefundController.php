<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RefundRequest;
use App\Models\Payment;
use App\Models\Refund;
use App\Traits\AlertMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefundController extends Controller
{
    use AlertMessage;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search ?? '';
        $status = $request->status ?? '';

        $refunds = Refund::query()
            ->with(['payment', 'payment.reservation', 'payment.reservation.user'])
            ->when($search, function($query) use ($search) {
                return $query->where('transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('payment.reservation.user', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($status !== '', function($query) use ($status) {
                return $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get refund statuses for filter
        $statuses = Refund::select('status')->distinct()->pluck('status');

        return view('admin.refund.index', compact('refunds', 'statuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get paid payments that don't have refunds yet
        $payments = Payment::where('payment_status', 'paid')
            ->whereDoesntHave('refund')
            ->with(['reservation', 'reservation.user'])
            ->get();

        return view('admin.refund.create', compact('payments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(RefundRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            $data['transaction_id'] = 'REF-' . time() . rand(100, 999);
            $data['status'] = $data['status'] ?? 'pending';

            // Ensure amount doesn't exceed payment amount
            $payment = Payment::find($data['payment_id']);
            if ($data['amount'] > $payment->amount) {
                $this->errorMessage('Jumlah refund tidak boleh melebihi jumlah pembayaran');
                return redirect()->back()->withInput();
            }

            $refund = Refund::create($data);

            // If refund is approved immediately, update payment status
            if ($refund->status === 'approved') {
                $payment->update(['payment_status' => 'refunded']);

                // Also update the reservation status
                $payment->reservation->update(['status' => 'refunded']);
            }

            DB::commit();
            $this->successMessage('Permintaan refund berhasil dibuat');
            return redirect()->route('admin.refund.index');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage('Error membuat permintaan refund: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Refund $refund)
    {
        $refund->load(['payment', 'payment.reservation', 'payment.reservation.user', 'payment.reservation.playstation']);
        return view('admin.refund.show', compact('refund'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Refund $refund)
    {
        $payments = Payment::where(function($query) use ($refund) {
                $query->where('payment_status', 'paid')
                    ->orWhere('id', $refund->payment_id);
            })
            ->with(['reservation', 'reservation.user'])
            ->get();

        return view('admin.refund.edit', compact('refund', 'payments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(RefundRequest $request, Refund $refund)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Ensure amount doesn't exceed payment amount
            $payment = Payment::find($data['payment_id']);
            if ($data['amount'] > $payment->amount) {
                $this->errorMessage('Jumlah refund tidak boleh melebihi jumlah pembayaran');
                return redirect()->back()->withInput();
            }

            $oldStatus = $refund->status;
            $refund->update($data);

            // If status changed to approved, update payment
            if ($oldStatus !== 'approved' && $refund->status === 'approved') {
                $payment->update(['payment_status' => 'refunded']);

                // Also update the reservation status
                $payment->reservation->update(['status' => 'refunded']);
            }

            DB::commit();
            $this->successMessage('Permintaan refund berhasil diperbarui');
            return redirect()->route('admin.refund.index');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage('Error memperbarui permintaan refund: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Refund $refund)
    {
        try {
            DB::beginTransaction();

            // If refund was approved, revert payment status
            if ($refund->status === 'approved') {
                $payment = Payment::find($refund->payment_id);
                $payment->update(['payment_status' => 'paid']);

                // Also revert the reservation status if necessary
                $payment->reservation->update(['status' => 'paid']);
            }

            $refund->delete();

            DB::commit();
            $this->successMessage('Permintaan refund berhasil dihapus');
            return redirect()->route('admin.refund.index');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage('Error menghapus permintaan refund: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Process refund approval/rejection
     */
    public function processRefund(Request $request, Refund $refund)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'status' => 'required|in:pending,approved,rejected'
            ]);

            $refund->update([
                'status' => $request->status,
                'admin_notes' => $request->admin_notes ?? $refund->admin_notes,
                'processed_at' => $request->status !== 'pending' ? now() : null
            ]);

            // If approved, update payment status
            if ($request->status === 'approved') {
                $payment = Payment::find($refund->payment_id);
                $payment->update(['payment_status' => 'refunded']);

                // Also update the reservation status
                $payment->reservation->update(['status' => 'refunded']);
            }

            DB::commit();
            $this->successMessage('Status refund berhasil diperbarui');
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage('Error memperbarui status refund: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
