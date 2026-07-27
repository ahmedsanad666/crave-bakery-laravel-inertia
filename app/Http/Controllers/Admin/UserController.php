<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InviteAdminRequest;
use App\Http\Requests\UpdateAdminPermissionsRequest;
use App\Http\Resources\AdminInvitationResource;
use App\Http\Resources\AdminUserResource;
use App\Models\AdminInvitation;
use App\Models\AdminUser;
use App\Services\AdminInvitationService;
use App\Services\AdminUserService;
use App\Support\AdminPermissions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly AdminUserService $adminUserService,
        private readonly AdminInvitationService $invitationService,
    ) {}

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', AdminUser::class);

        $status = $request->string('status')->toString();

        $filters = [
            'search' => $request->string('search')->toString(),
            'role' => $request->string('role')->toString(),
            'status' => $status === 'pending' ? '' : $status,
            'per_page' => $request->integer('per_page', 15),
        ];

        $admins = $this->adminUserService->paginate($filters);

        return Inertia::render('Admin/Users/Index', [
            'admins' => AdminUserResource::collection($admins),
            'pendingInvitations' => AdminInvitationResource::collection(
                $this->invitationService->listPending(),
            )->resolve(),
            'stats' => $this->adminUserService->stats(),
            'filters' => [
                'search' => $filters['search'],
                'role' => $filters['role'],
                'status' => $status,
            ],
            'permissionTemplates' => AdminPermissions::templateNames(),
            'permissionSchema' => AdminPermissions::grantableScopes(),
        ]);
    }

    public function invite(InviteAdminRequest $request): RedirectResponse
    {
        $this->authorize('invite', AdminUser::class);

        $this->invitationService->invite(
            $request->validated('email'),
            $request->validated('role'),
            $request->user(),
            $request->validated('template'),
            $request->validated('permissions'),
        );

        return back()->with('success', 'Invitation sent.');
    }

    public function resendInvitation(AdminInvitation $invitation): RedirectResponse
    {
        $this->authorize('resendInvitation', AdminUser::class);

        $this->invitationService->resend($invitation);

        return back()->with('success', 'Invitation resent.');
    }

    public function revokeInvitation(AdminInvitation $invitation): RedirectResponse
    {
        $this->authorize('resendInvitation', AdminUser::class);

        $this->invitationService->revoke($invitation);

        return back()->with('success', 'Invitation revoked.');
    }

    public function permissions(AdminUser $adminUser): Response
    {
        $this->authorize('view', $adminUser);

        return Inertia::render('Admin/Users/Permissions', [
            'admin' => (new AdminUserResource($adminUser))->resolve(),
            'schema' => AdminPermissions::grantableScopes(),
            'templates' => AdminPermissions::templateNames(),
        ]);
    }

    public function updatePermissions(
        UpdateAdminPermissionsRequest $request,
        AdminUser $adminUser,
    ): RedirectResponse {
        $this->adminUserService->updatePermissions(
            $adminUser,
            $request->validated('permissions'),
        );

        return back()->with('success', 'Permissions updated.');
    }

    public function deactivate(Request $request, AdminUser $adminUser): RedirectResponse
    {
        $this->authorize('deactivate', $adminUser);

        $this->adminUserService->deactivate($adminUser, $request->user());

        return back()->with('success', 'Admin deactivated.');
    }

    public function destroy(Request $request, AdminUser $adminUser): RedirectResponse
    {
        $this->authorize('delete', $adminUser);

        $this->adminUserService->delete($adminUser, $request->user());

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Admin removed.');
    }
}
