<?php

namespace App\View\Composers;

use App\Enum\Role;
use App\Models\Santri;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class SantriModernLayoutComposer
{
    /**
     * @var array<int, string>
     */
    private const LOGO_CANDIDATES = [
        'assets/images/logo-ppm.png',
        'assets/images/logo_ppm.png',
        'assets/images/logo.png',
    ];

    private static bool $logoResolved = false;
    private static ?string $cachedLogoRel = null;

    public function compose(View $view): void
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();
        $roleValue = $currentUser?->role?->value;

        $isWali = $roleValue === Role::WALI->value;
        $isSantri = $roleValue === Role::SANTRI->value;
        $isStaffRole = in_array($roleValue, [Role::PENGURUS->value, Role::DEWAN_GURU->value], true);
        $isKetertiban = $isSantri && (bool) $currentUser?->isKetertiban();

        $waliChildren = $isWali ? collect($currentUser?->waliOf ?? []) : collect();
        $activeChildCode = $this->resolveActiveChildCode($isWali, $waliChildren);
        $hasChildSelected = filled($activeChildCode);

        $santriTeam = $this->resolveSantriTeam(
            isSantri: $isSantri,
            isWali: $isWali,
            hasChildSelected: $hasChildSelected,
            activeChildCode: $activeChildCode,
            currentUser: $currentUser,
            waliChildren: $waliChildren
        );

        $santriTeamBadge = User::teamAbbreviation($santriTeam);

        $sidebarRoleCaption = match ($roleValue) {
            Role::DEWAN_GURU->value => 'Dewan Guru KH2',
            Role::PENGURUS->value => 'Pengurus KH2',
            Role::WALI->value => 'Wali Santri KH2',
            default => 'Tim: ' . ($santriTeam !== '' ? $santriTeam : '-'),
        };

        $defaultPresensiMode = $isStaffRole ? 'team' : 'mine';
        $teamFeatureBadge = $santriTeamBadge !== '' ? $santriTeamBadge : 'KTB';
        $isPresensiPrimaryActive = request()->routeIs('santri.presensi.index')
            && request()->query('mode', $defaultPresensiMode) === $defaultPresensiMode;
        $logMode = request()->query('mode');
        $isLogRoute = request()->routeIs('santri.data.log');
        $isLogInputActive = $isLogRoute && ($logMode === null || $logMode === '' || $logMode === 'input');
        $isLogMineActive = $isLogRoute && $logMode === 'mine';
        $brandRoute = $isWali ? route('wali.main') : route('santri.home');
        $logoRel = $this->resolveLogoRel();
        $waliMenu = [
            ['label' => 'Dashboard Anak', 'icon' => 'layout-dashboard', 'route' => 'wali.anak.overview'],
            ['label' => 'Presensi', 'icon' => 'fingerprint', 'route' => 'wali.anak.presensi'],
            ['label' => 'Progress Keilmuan', 'icon' => 'calendar', 'route' => 'wali.anak.progres'],
            ['label' => 'Log Keluar/Masuk', 'icon' => 'clock', 'route' => 'wali.anak.log'],
        ];

        $view->with([
            'currentUser' => $currentUser,
            'roleValue' => $roleValue,
            'isWali' => $isWali,
            'isSantri' => $isSantri,
            'isStaffRole' => $isStaffRole,
            'isKetertiban' => $isKetertiban,
            'activeChildCode' => $activeChildCode,
            'hasChildSelected' => $hasChildSelected,
            'santriTeam' => $santriTeam,
            'santriTeamBadge' => $santriTeamBadge,
            'sidebarRoleCaption' => $sidebarRoleCaption,
            'defaultPresensiMode' => $defaultPresensiMode,
            'teamFeatureBadge' => $teamFeatureBadge,
            'isPresensiPrimaryActive' => $isPresensiPrimaryActive,
            'isLogRoute' => $isLogRoute,
            'isLogInputActive' => $isLogInputActive,
            'isLogMineActive' => $isLogMineActive,
            'brandRoute' => $brandRoute,
            'logoRel' => $logoRel,
            'waliMenu' => $waliMenu,
        ]);
    }

    private function resolveActiveChildCode(bool $isWali, Collection $waliChildren): ?string
    {
        $activeChildRaw = request()->route('santriCode')
            ?? request()->route('santri')
            ?? request()->route('code');

        $activeChildCode = $activeChildRaw instanceof Santri
            ? $activeChildRaw->code
            : (is_scalar($activeChildRaw) ? (string) $activeChildRaw : null);

        if ($isWali && blank($activeChildCode)) {
            $activeChildCode = $waliChildren->sortBy('nama_lengkap')->first()?->code;
        }

        return filled($activeChildCode) ? (string) $activeChildCode : null;
    }

    private function resolveSantriTeam(
        bool $isSantri,
        bool $isWali,
        bool $hasChildSelected,
        ?string $activeChildCode,
        ?User $currentUser,
        Collection $waliChildren
    ): string {
        if ($isSantri) {
            return trim((string) ($currentUser?->teamName() ?? ''));
        }

        if ($isWali && $hasChildSelected) {
            $selectedChild = $waliChildren->firstWhere('code', (string) $activeChildCode);

            return trim((string) ($selectedChild?->tim ?? ''));
        }

        return '';
    }

    private function resolveLogoRel(): ?string
    {
        if (self::$logoResolved) {
            return self::$cachedLogoRel;
        }

        foreach (self::LOGO_CANDIDATES as $candidate) {
            if (file_exists(public_path($candidate))) {
                self::$cachedLogoRel = $candidate;
                self::$logoResolved = true;

                return self::$cachedLogoRel;
            }
        }

        self::$logoResolved = true;
        self::$cachedLogoRel = null;

        return self::$cachedLogoRel;
    }
}
