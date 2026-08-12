<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePromoCodeRequest;
use App\Http\Requests\UpdatePromoCodeRequest;
use App\Http\Resources\PromoCodeResource;
use App\Models\PromoCode;
use App\Services\PromoCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PromoCodeController extends Controller
{
    public function __construct(
        private readonly PromoCodeService $promoCodeService,
    ) {
        $this->authorizeResource(PromoCode::class, 'promo_code');
    }

    public function index(Request $request): Response
    {
        $search = $request->string('search')->toString() ?: null;
        $status = $request->string('status')->toString() ?: null;

        $promoCodes = PromoCode::query()
            ->search($search)
            ->status($status)
            ->ordered()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Admin/PromoCodes/Index', [
            'stats' => $this->promoCodeService->stats(),
            'filters' => [
                'search' => $search ?? '',
                'status' => $status ?? '',
            ],
            'promoCodes' => PromoCodeResource::collection($promoCodes),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/PromoCodes/Create');
    }

    public function store(StorePromoCodeRequest $request): RedirectResponse
    {
        $this->promoCodeService->create($request->validated());

        return redirect()
            ->route('admin.promo-codes.index')
            ->with('success', 'Promo code created successfully.');
    }

    public function edit(PromoCode $promoCode): Response
    {
        return Inertia::render('Admin/PromoCodes/Edit', [
            'promoCode' => (new PromoCodeResource($promoCode))->resolve(),
        ]);
    }

    public function update(UpdatePromoCodeRequest $request, PromoCode $promoCode): RedirectResponse
    {
        $this->promoCodeService->update($promoCode, $request->validated());

        return redirect()
            ->route('admin.promo-codes.index')
            ->with('success', 'Promo code updated successfully.');
    }

    public function destroy(PromoCode $promoCode): RedirectResponse
    {
        $this->promoCodeService->delete($promoCode);

        return redirect()
            ->route('admin.promo-codes.index')
            ->with('success', 'Promo code deleted successfully.');
    }

    public function toggle(PromoCode $promoCode): RedirectResponse
    {
        $this->authorize('update', $promoCode);

        $this->promoCodeService->toggleActive($promoCode);

        return back()->with('success', 'Promo code status updated.');
    }
}
