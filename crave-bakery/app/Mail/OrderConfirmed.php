<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Order $order,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order confirmed — '.$this->order->order_number,
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->htmlBody(),
        );
    }

    private function htmlBody(): string
    {
        $orderNumber = e($this->order->order_number);
        $total = number_format((float) $this->order->total, 2);
        $eta = $this->order->estimated_delivery_at?->toFormattedDateString() ?? 'soon';
        $url = route('orders.show', $this->order);

        return <<<HTML
        <div style="font-family: Inter, sans-serif; color: #1c1b1b; line-height: 1.6;">
            <h1 style="font-family: Georgia, serif; color: #3D1A0E;">Crave Bakery</h1>
            <p>Thanks for your order!</p>
            <p>Your order <strong>{$orderNumber}</strong> has been placed successfully.</p>
            <p>Total: <strong>\${$total}</strong></p>
            <p>Estimated delivery: <strong>{$eta}</strong></p>
            <p><a href="{$url}" style="display:inline-block;padding:12px 24px;background:#E8572A;color:#fff;border-radius:999px;text-decoration:none;font-weight:600;">View order</a></p>
        </div>
        HTML;
    }
}
