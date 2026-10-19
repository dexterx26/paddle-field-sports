<?php

namespace App\Http\Controllers;

use App\Models\CreditTopup;
use App\Models\CreditTransaction;
use App\Models\VenueSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreditController extends Controller
{
    /**
     * Submit a new Credit Top-Up request with uploaded payment receipt proof
     */
    public function submitTopup(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please sign in to top up your credits.');
        }

        $request->validate([
            'credits' => 'required|numeric|min:0.5|max:50000',
            'receipt' => 'required|image|mimes:jpeg,png,jpg,webp|max:10240',
            'payment_method' => 'nullable|string|max:50',
            'customer_notes' => 'nullable|string|max:500',
        ]);

        $settings = VenueSetting::getSettings();
        $conversionRate = (float) ($settings->credit_conversion_rate ?: 150.00);
        $credits = (float) $request->input('credits');
        $amountPhp = round($credits * $conversionRate, 2);

        // Store securely on private disk (not accessible via public HTTP URL)
        $receiptPath = $request->file('receipt')->store('credit_receipts');

        $reference = 'CR-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        $topup = CreditTopup::create([
            'reference' => $reference,
            'user_id' => $user->id,
            'amount_php' => $amountPhp,
            'credits' => $credits,
            'conversion_rate' => $conversionRate,
            'payment_method' => $request->input('payment_method', 'manual_receipt'),
            'receipt_image_path' => $receiptPath,
            'status' => 'pending',
            'customer_notes' => $request->input('customer_notes'),
        ]);

        $message = "Your top-up request for {$topup->formatted_credits} (₱" . number_format($amountPhp, 2) . ") has been submitted! Reference: {$reference}. The court owner will verify your receipt shortly.";

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'reference' => $reference,
                'topup' => $topup,
            ]);
        }

        return redirect()->route('profile.edit', ['#credits-wallet'])
            ->with('success', $message);
    }

    /**
     * Cancel a pending top-up request
     */
    public function cancelTopup(int $id)
    {
        $topup = CreditTopup::where('user_id', Auth::id())
            ->where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        $topup->delete();

        return back()->with('info', "Top-up request {$topup->reference} has been cancelled.");
    }

    /**
     * Get real-time credit balance and conversion rate info
     */
    public function getBalance()
    {
        $user = Auth::user();
        $settings = VenueSetting::getSettings();

        return response()->json([
            'success' => true,
            'is_logged_in' => $user !== null,
            'credits' => $user ? (float) $user->credits : 0,
            'formatted_credits' => $user ? $user->formatted_credits : '0.0 Credits',
            'conversion_rate' => (float) ($settings->credit_conversion_rate ?: 150),
            'formatted_rate' => '₱' . number_format($settings->credit_conversion_rate ?: 150, 2) . ' = 1 Credit',
            'manual_account_name' => $settings->manual_account_name,
            'manual_account_number' => $settings->manual_account_number,
            'manual_payment_qr' => $settings->manual_payment_qr ? asset('storage/' . $settings->manual_payment_qr) : null,
        ]);
    }
}
