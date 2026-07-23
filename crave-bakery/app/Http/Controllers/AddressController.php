<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddressRequest;
use App\Http\Requests\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Http\Resources\ProfileResource;
use App\Models\Address;
use App\Services\AddressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AddressController extends Controller
{
    public function __construct(private readonly AddressService $addressService)
    {
    }

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Address::class);

        $addresses = $this->addressService->listForUser($request->user());

        return Inertia::render('Profile/Addresses', [
            'addresses' => AddressResource::collection($addresses)->resolve(),
            'user' => (new ProfileResource($request->user()))->resolve(),
        ]);
    }

    public function store(StoreAddressRequest $request): RedirectResponse
    {
        $this->addressService->create(
            $request->user(),
            $request->validated(),
        );

        return back()->with('success', 'Address added successfully.');
    }

    public function update(UpdateAddressRequest $request, Address $address): RedirectResponse
    {
        $this->addressService->update($address, $request->validated());

        return back()->with('success', 'Address updated successfully.');
    }

    public function destroy(Request $request, Address $address): RedirectResponse
    {
        $this->authorize('delete', $address);

        $this->addressService->delete($address);

        return back()->with('success', 'Address deleted successfully.');
    }

    public function setDefault(Request $request, Address $address): RedirectResponse
    {
        $this->authorize('update', $address);

        $this->addressService->setDefault($address);

        return back()->with('success', 'Default address updated.');
    }
}
