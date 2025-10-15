<?php

namespace App\Http\Middleware;

use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $permissions = $user?->getAllPermissions()->pluck('name')->toArray() ?? [];

        $mustCompleteProfile = $user?->mustChangePassword() || empty($user?->profile_photo_path);

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'can' => $permissions,
            ],
            'app' => [
                'mustCompleteProfile' => $mustCompleteProfile,
                'lowStockCount' => $user ? Cache::remember("low-stock-count-{$user->id}", now()->addMinutes(5), fn () => app(InventoryService::class)->outOfStockProducts()->count()) : 0,
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
