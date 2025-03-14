<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use App\Models\Reservation;
use App\Traits\AlertMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    use AlertMessage;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->search ?? '';
        $status = $request->payment_status ?? '';

        $payments = Payment::query()
            ->with(['reservation', 'reservation.user'])
            ->when($search, function($query) use ($search) {
                return $query->where('order_id', 'like', "%{$search}%")
                    ->orWhere('transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('reservation.user', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            })
            ->when($status !== '', function($query) use ($status) {
                return $query->where('payment_status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Get payment statuses for filter
        $paymentStatuses = Payment::select('payment_status')
            ->distinct()
            ->pluck('payment_status')
            ->toArray();

        return view('admin.payment.index', compact('payments', 'paymentStatuses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $reservations = Reservation::where('status', 'confirmed')
            ->whereDoesntHave('payment', function($query) {
                $query->where('payment_status', 'paid');
            })
            ->with('user')
            ->get();

        return view('admin.payment.create', compact('reservations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PaymentRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // Generate order ID if not provided
            if (!isset($data['order_id']) || empty($data['order_id'])) {
                $data['order_id'] = 'ORDER-' . time() . rand(100, 999);
            }

            // Set payment date to now if not provided
            if (!isset($data['payment_date']) || empty($data['payment_date'])) {
                $data['payment_date'] = now();
            }

            $payment = Payment::create($data);

            // If payment is paid, update reservation status
            if ($payment->payment_status === 'paid') {
                $reservation = Reservation::find($payment->reservation_id);
                $reservation->update(['status' => 'paid']);
            }

            DB::commit();
            $this->successMessage('Payment recorded successfully');
            return redirect()->route('admin.payment.index');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage('Error recording payment: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        $payment->load(['reservation', 'reservation.user', 'reservation.playstation']);
        return view('admin.payment.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        $reservations = Reservation::where(function($query) use ($payment) {
                $query->where('status', 'confirmed')
                    ->orWhere('id', $payment->reservation_id);
            })
            ->with('user')
            ->get();

        return view('admin.payment.edit', compact('payment', 'reservations'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PaymentRequest $request, Payment $payment)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            $oldStatus = $payment->payment_status;
            $payment->update($data);

            // If payment status changed to paid, update reservation status
            if ($oldStatus !== 'paid' && $payment->payment_status === 'paid') {
                $reservation = Reservation::find($payment->reservation_id);
                $reservation->update(['status' => 'paid']);
            }

            // If payment status changed to refunded
            if ($oldStatus !== 'refunded' && $payment->payment_status === 'refunded') {
                $reservation = Reservation::find($payment->reservation_id);
                $reservation->update(['status' => 'refunded']);
            }

            DB::commit();
            $this->successMessage('Payment updated successfully');
            return redirect()->route('admin.payment.index');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->errorMessage('Error updating payment: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        try {
            // Check if payment has refund
            if ($payment->refund()->exists()) {
                $this->errorMessage('Cannot delete payment with existing refund');
                return redirect()->back();
            }

            $payment->delete();

            $this->successMessage('Payment deleted successfully');
            return redirect()->route('admin.payment.index');
        } catch (\Exception $e) {
            $this->errorMessage('Error deleting payment: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    /**
     * Generate invoice for payment
     */
    public function generateInvoice(Payment $payment)
    {
        $payment->load(['reservation', 'reservation.user', 'reservation.playstation']);

        return view('admin.payment.invoice', compact('payment'));
    }

    /**
     * Update payment status
     */
    public function updateStatus(Request $request, Payment $payment)
    {
        try {
            $request->validate([
                'payment_status' => 'required|in:pending,paid,failed,refunded,expire',
            ]);

            $oldStatus = $payment->payment_status;
            $payment->update(['payment_status' => $request->payment_status]);

            // Update reservation status based on payment status
            if ($oldStatus !== 'paid' && $payment->payment_status === 'paid') {
                $payment->reservation->update(['status' => 'paid']);
            } elseif ($payment->payment_status === 'refunded') {
                $payment->reservation->update(['status' => 'refunded']);
            } elseif ($payment->payment_status === 'failed' || $payment->payment_status === 'expire') {
                $payment->reservation->update(['status' => 'cancelled']);
            }

            $this->successMessage('Payment status updated successfully');
            return redirect()->back();
        } catch (\Exception $e) {
            $this->errorMessage('Error updating payment status: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
