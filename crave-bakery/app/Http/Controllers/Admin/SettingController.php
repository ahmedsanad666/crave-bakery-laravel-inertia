<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSiteSettingsRequest;
use App\Models\SiteSetting;
use App\Services\SiteSettingService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function __construct(private readonly SiteSettingService $siteSettingService)
    {
    }

    public function index(): Response
    {
        $this->authorize('viewAny', SiteSetting::class);

        $payload = $this->siteSettingService->allForAdmin();

        return Inertia::render('Admin/Settings/Index', [
            'settings' => $payload['settings'],
            'palettes' => $payload['palettes'],
            'fonts' => $payload['fonts'],
            'resolvedPalette' => $payload['resolved_palette'],
        ]);
    }

    public function update(UpdateSiteSettingsRequest $request): RedirectResponse
    {
        $this->siteSettingService->update(
            $request->settingsPayload(),
            $request->file('logo'),
            $request->file('favicon'),
            $request->file('hero_image'),
        );

        return back()->with('success', 'Website settings saved.');
    }
}
