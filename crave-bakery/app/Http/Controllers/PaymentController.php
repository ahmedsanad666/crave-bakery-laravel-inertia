<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmPaymentRequest;
use App\Http\Requests\CreatePaymentIntentRequest;
use App\Services\PaymentService;
use App\Services\StripePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly StripePaymentService $stripePaymentService,
    ) {
    }

    public function createIntent(CreatePaymentIntentRequest $request): JsonResponse
    {
        try {
            $gateway = $this->paymentService->resolve('stripe');
            $result = $gateway->createIntent($request->user());
        } catch (ValidationException $e) {
            return response()->json([
                'error' => collect($e->errors())->flatten()->first()
                    ?? 'Unable to initialise payment. Please try again.',
            ], 422);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'error' => 'Card payments are currently unavailable.',
            ], 422);
        }

        return response()->json($result);
    }

    public function confirm(ConfirmPaymentRequest $request): RedirectResponse
    {
        try {
            // Ensure Stripe credentials from DB are applied before verify/fulfill
            $this->paymentService->resolve('stripe');

            $order = $this->stripePaymentService->confirmPayment(
                $request->validated('payment_intent_id'),
                $request->user(),
            );
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first()
                ?? 'Payment could not be confirmed.';

            return redirect()
                ->route('checkout.payment')
                ->with('error', $message);
        }

        return redirect()
            ->route('checkout.confirmation', $order)
            ->with('success', 'Payment successful. Order confirmed.');
    }
}
