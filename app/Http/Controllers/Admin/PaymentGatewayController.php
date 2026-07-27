<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePaymentGatewayRequest;
use App\Models\PaymentGatewayModel;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PaymentGatewayController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', PaymentGatewayModel::class);

        $gateways = PaymentGatewayModel::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PaymentGatewayModel $gateway) => [
                'id' => $gateway->id,
                'name' => $gateway->name,
                'label' => $gateway->label,
                'description' => $gateway->description,
                'logo' => $gateway->logo,
                'is_enabled' => $gateway->is_enabled,
                'is_test_mode' => $gateway->is_test_mode,
                'instructions' => $gateway->instructions,
                'sort_order' => $gateway->sort_order,
                // Never return actual secret values — only whether each field is filled
                'config_fields' => $this->getConfigFields($gateway->name, $gateway->config),
            ]);

        return Inertia::render('Admin/Settings/Payments', [
            'gateways' => $gateways,
        ]);
    }

    public function update(
        UpdatePaymentGatewayRequest $request,
        PaymentGatewayModel $gateway,
    ): RedirectResponse {
        $validated = $request->validated();

        // Merge new config with existing — empty fields keep the current secret
        if (array_key_exists('config', $validated)) {
            $existingConfig = $gateway->config;
            $newConfig = array_filter(
                $validated['config'] ?? [],
                fn ($value) => $value !== null && $value !== '',
            );
            $validated['config'] = array_merge($existingConfig, $newConfig);
        }

        $gateway->update($validated);

        return redirect()
            ->route('admin.settings.payments')
            ->with('success', "{$gateway->label} settings updated successfully.");
    }

    public function toggle(PaymentGatewayModel $gateway): RedirectResponse
    {
        $this->authorize('update', $gateway);

        $gateway->update(['is_enabled' => ! $gateway->is_enabled]);

        $status = $gateway->is_enabled ? 'enabled' : 'disabled';

        return redirect()
            ->route('admin.settings.payments')
            ->with('success', "{$gateway->label} has been {$status}.");
    }

    /**
     * @param  array<string, mixed>  $config
     * @return list<array{key: string, label: string, type: string, filled: bool}>
     */
    private function getConfigFields(string $gatewayName, array $config): array
    {
        return match ($gatewayName) {
            'stripe' => [
                [
                    'key' => 'key',
                    'label' => 'Publishable Key',
                    'type' => 'text',
                    'filled' => ! empty($config['key']),
                ],
                [
                    'key' => 'secret',
                    'label' => 'Secret Key',
                    'type' => 'password',
                    'filled' => ! empty($config['secret']),
                ],
                [
                    'key' => 'webhook_secret',
                    'label' => 'Webhook Secret',
                    'type' => 'password',
                    'filled' => ! empty($config['webhook_secret']),
                ],
            ],
            'cod' => [],
            default => [],
        };
    }
}
