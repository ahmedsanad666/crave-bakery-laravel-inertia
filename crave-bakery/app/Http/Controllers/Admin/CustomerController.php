<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminCustomerResource;
use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerService $customerService,
    ) {
        $this->authorizeResource(User::class, 'customer');
    }

    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
            'per_page' => $request->integer('per_page', 15),
        ];

        $customers = $this->customerService->paginate($filters);

        return Inertia::render('Admin/Customers/Index', [
            'customers' => AdminCustomerResource::collection($customers),
            'stats' => $this->customerService->stats(),
            'filters' => [
                'search' => $filters['search'],
                'status' => $filters['status'],
            ],
        ]);
    }

    public function show(User $customer): Response
    {
        $customer = $this->customerService->findForAdmin($customer);

        return Inertia::render('Admin/Customers/Show', [
            'customer' => (new AdminCustomerResource($customer))->resolve(),
        ]);
    }
}
