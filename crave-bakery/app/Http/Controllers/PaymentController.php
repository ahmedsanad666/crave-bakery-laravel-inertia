<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConfirmPaymentRequest;
use App\Http\Requests\CreatePaymentIntentRequest;
use App\Services\StripePaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function __construct(
        private readonly StripePaymentService $stripePaymentService,
    ) {
    }

    public function createIntent(CreatePaymentIntentRequest $request): JsonResponse
    {
        try {
            $result = $this->stripePaymentService->createPaymentIntent(
                $request->user(),
            );
        } catch (ValidationException $e) {
            return response()->json([
                'error' => collect($e->errors())->flatten()->first()
                    ?? 'Unable to initialise payment. Please try again.',
            ], 422);
        }

        return response()->json($result);
    }

    public function confirm(ConfirmPaymentRequest $request): RedirectResponse
    {
        try {
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
