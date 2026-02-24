<?php

namespace App\Http\Controllers\Santri;

use App\Enum\Role;
use App\Http\Controllers\Controller;
use App\Models\ProgressKeilmuan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ProgressKeilmuanController extends Controller
{
    private const CATEGORY_QURAN = 'al-quran';
    private const CATEGORY_HADITS = 'al-hadits';

    public function index(Request $request): View|RedirectResponse
    {
        $user = auth()->user();
        if ($user?->role === Role::WALI) {
            $firstChildCode = $user->waliOf()
                ->orderBy('santris.nama_lengkap')
                ->value('santris.code');

            if (filled($firstChildCode)) {
                return redirect()->route('wali.anak.progres', ['santriCode' => $firstChildCode]);
            }

            return redirect()->route('profile.edit')->with('status', 'Akun wali belum terhubung ke data anak.');
        }

        $santri = $this->requireSantri();
        $category = $this->resolveCategory((string) $request->input('category'));

        $modules = collect($this->modules($category));

        $records = ProgressKeilmuan::where('santri_id', $santri->id)
            ->where('level', $category)
            ->get()
            ->keyBy('judul');

        $items = $modules->mapWithKeys(function (array $entry) use ($records) {
            $record = $records->get($entry['judul'] ?? $entry[0] ?? null);
            $title = $entry['judul'] ?? $entry[0] ?? '';
            $target = $entry['target'] ?? 0;
            $value = $record?->capaian;
            $percent = $value !== null && $target > 0 ? (int) min(100, round(($value / $target) * 100)) : 0;

            return [$title => [
                'judul' => $title,
                'target' => $target,
                'value' => $value,
                'persentase' => $percent,
                'updated_at' => $record?->updated_at,
            ]];
        });

        $stats = $this->buildStats($items);

        $recentUpdates = ProgressKeilmuan::where('santri_id', $santri->id)
            ->where('level', $category)
            ->latest('updated_at')
            ->take(5)
            ->get();

        return view('santri.pages.data.progres', [
            'santri' => $santri,
            'category' => $category,
            'items' => $items,
            'modules' => $modules,
            'stats' => $stats,
            'recentUpdates' => $recentUpdates,
        ]);
    }

    public function sync(Request $request): RedirectResponse
    {
        $santri = $this->requireSantri();
        $category = $this->resolveCategory((string) $request->input('category'));
        $modules = collect($this->modules($category))->keyBy('judul');

        $valueRules = ['nullable', 'integer', 'min:0'];
        if ($category === self::CATEGORY_QURAN) {
            $valueRules[] = 'max:20';
        }

        $payload = $request->validate([
            'category' => ['required', 'in:' . self::CATEGORY_QURAN . ',' . self::CATEGORY_HADITS],
            'modules' => ['required', 'array'],
            'modules.*.judul' => ['required', 'string'],
            'modules.*.value' => $valueRules,
        ]);

        $errors = [];
        foreach ($payload['modules'] as $row) {
            if (! $modules->has($row['judul'])) {
                continue;
            }

            $target = $modules[$row['judul']]['target'];
            $value = $row['value'] ?? null;

            if ($value !== null && $value !== '' && $value > $target) {
                $errors[] = 'Progres ' . $row['judul'] . ' maksimal ' . $target . ' halaman.';
            }
        }

        if (! empty($errors)) {
            return back()->withErrors($errors)->withInput();
        }

        $existing = ProgressKeilmuan::where('santri_id', $santri->id)
            ->where('level', $category)
            ->get()
            ->keyBy('judul');

        $timestamp = now(config('app.timezone'));

        $this->authorize('create', ProgressKeilmuan::class);

        foreach ($payload['modules'] as $row) {
            if (! $modules->has($row['judul'])) {
                continue;
            }

            $value = $row['value'] ?? null;
            $target = $modules[$row['judul']]['target'];
            $record = $existing->get($row['judul']);

            if ($value === null || $value === '') {
                if ($record) {
                    $this->authorize('delete', $record);
                    $record->delete();
                }
                continue;
            }

            $value = (int) $value;

            if ($record) {
                $this->authorize('update', $record);
                $record->update([
                    'capaian' => $value,
                    'target' => $target,
                    'satuan' => 'halaman',
                    'level' => $category,
                    'judul' => $row['judul'],
                    'terakhir_setor' => $timestamp,
                ]);
            } else {
                ProgressKeilmuan::create([
                    'santri_id' => $santri->id,
                    'judul' => $row['judul'],
                    'target' => $target,
                    'capaian' => $value,
                    'satuan' => 'halaman',
                    'level' => $category,
                    'terakhir_setor' => $timestamp,
                ]);
            }
        }

        return back()->with('success', 'Progres keilmuan berhasil diperbarui.');
    }

    private function buildStats(Collection $items): array
    {
        $total = $items->count();
        $avg = $total ? (int) round($items->avg(fn ($item) => $item['persentase'])) : 0;

        return [
            'total' => $total,
            'completed' => $items->filter(fn ($item) => ($item['persentase'] ?? 0) >= 100)->count(),
            'inProgress' => $items->filter(fn ($item) => ($item['persentase'] ?? 0) > 0 && ($item['persentase'] ?? 0) < 100)->count(),
            'average' => $avg,
        ];
    }

    private function resolveCategory(?string $category): string
    {
        return in_array($category, [self::CATEGORY_QURAN, self::CATEGORY_HADITS], true)
            ? $category
            : self::CATEGORY_QURAN;
    }

    private function modules(string $category): array
    {
        return $category === self::CATEGORY_HADITS
            ? $this->haditsList()
            : $this->quranList();
    }

    private function quranList(): array
    {
        return array_map(fn ($i) => ['judul' => 'Juz ' . $i, 'target' => 20], range(1, 30));
    }

    private function haditsList(): array
    {
        return [
            ["judul" => "Mukhtaru Da'awat", 'target' => 171],
            ['judul' => 'Buku Saku Tata Krama', 'target' => 60],
            ['judul' => 'Al Khulasoh Fii Adabith Tholib', 'target' => 20],
            ['judul' => 'Materi Kelas Bacaan', 'target' => 37],
            ['judul' => 'K. Sholah', 'target' => 151],
            ["judul" => "Al-Khulashoh Fil Imla'", 'target' => 20],
            ["judul" => "Luzumul Jama'ah", 'target' => 40],
            ['judul' => 'Materi Kelas Pegon', 'target' => 30],
            ['judul' => 'K. Sholatin Nawafil', 'target' => 98],
            ['judul' => 'K. Shoum', 'target' => 98],
            ['judul' => 'Materi Kelas Lambatan', 'target' => 47],
            ["judul" => "K. Da'awat", 'target' => 65],
            ['judul' => 'K. Adab', 'target' => 95],
            ['judul' => 'K. Shifatil Jannati Wannar', 'target' => 85],
            ['judul' => 'K. Janaiz', 'target' => 79],
            ['judul' => 'K. Adillah', 'target' => 96],
            ['judul' => 'K. Manasik wal Jihad', 'target' => 51],
            ['judul' => 'Materi Kelas Cepatan', 'target' => 70],
            ['judul' => 'K. Haji', 'target' => 111],
            ['judul' => 'K. Manaskil Haji', 'target' => 116],
            ['judul' => 'K. Ahkam', 'target' => 124],
            ['judul' => 'K. Jihad', 'target' => 63],
            ['judul' => 'K. Imaroh', 'target' => 104],
            ['judul' => 'K. Imaroh min Kanzil ummal', 'target' => 122],
            ['judul' => 'Khutbah', 'target' => 152],
            ['judul' => 'Materi Kelas Saringan', 'target' => 63],
            ['judul' => 'Hidayatul Mustafidz Fit-Tajwid', 'target' => 98],
            ['judul' => 'K. Nikah', 'target' => 101],
            ['judul' => 'K. Faroidh', 'target' => 134],
            ['judul' => 'Syarah Asmaullohul Husna', 'target' => 39],
            ["judul" => "Syarah Do'a ASAD", 'target' => 35],
        ];
    }

    private function requireSantri()
    {
        $santri = auth()->user()?->santri;
        abort_unless($santri, 403, 'Hanya santri yang dapat mengelola progres keilmuan.');

        return $santri;
    }

}
