<div class="shrink-0 grid grid-cols-1 gap-2 sm:grid-cols-2 xl:grid-cols-4">
  <div class="flex h-[72px] flex-col justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 shadow-sm">
    <p class="text-[11px] leading-4 text-gray-500">Total Santri</p>
    <p class="mt-1 text-[34px] font-semibold leading-none text-gray-900 tabular-nums">{{ $stats['totalSantri'] }}</p>
  </div>
  <div class="flex h-[72px] flex-col justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 shadow-sm">
    <p class="text-[11px] leading-4 text-gray-500">Santri Aktif Progres</p>
    <p class="mt-1 text-[34px] font-semibold leading-none text-gray-900 tabular-nums">{{ $stats['activeSantri'] }}</p>
  </div>
  <div class="flex h-[72px] flex-col justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 shadow-sm">
    <p class="text-[11px] leading-4 text-gray-500">Rata-rata Kelas</p>
    <p class="mt-1 text-[34px] font-semibold leading-none text-gray-900 tabular-nums">{{ $stats['average'] }}%</p>
  </div>
  <div class="flex h-[72px] flex-col justify-center rounded-xl border border-gray-200 bg-white px-4 py-2 shadow-sm">
    <p class="text-[11px] leading-4 text-gray-500">Modul Tuntas | Target {{ $stats['moduleCount'] }}</p>
    <p class="mt-1 text-[34px] font-semibold leading-none text-gray-900 tabular-nums">{{ $stats['completedModules'] }}</p>
  </div>
</div>

<div class="flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
  <div class="overflow-x-auto">
    <table class="min-w-full table-fixed text-[13px] leading-5">
      <thead class="bg-gray-50 text-[11px] uppercase tracking-wide text-gray-500">
        <tr>
          <th class="w-[34%] px-4 py-1 text-left">Santri</th>
          <th class="w-[9%] px-3 py-1 text-left">Kelas</th>
          <th class="w-[12%] px-3 py-1 text-left">Tim</th>
          <th class="w-[10%] px-3 py-1 text-right">Selesai</th>
          <th class="w-[13%] px-3 py-1 text-right">Dikerjakan</th>
          <th class="w-[10%] px-3 py-1 text-right">Rata-rata</th>
          <th class="w-[12%] px-3 py-1 text-left">Lihat Detail</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200">
        @forelse($rows as $row)
          <tr class="h-[42px]">
            <td class="px-4 py-1 align-middle">
              <p class="truncate whitespace-nowrap text-[11px] font-semibold leading-4 text-gray-900">{{ $row['nama'] }}</p>
            </td>
            <td class="px-3 py-1 align-middle text-gray-700">{{ $row['kelas'] }}</td>
            <td class="px-3 py-1 align-middle text-gray-700">{{ $row['tim'] }}</td>
            <td class="px-3 py-1 align-middle text-right font-medium text-gray-800 tabular-nums">{{ $row['completed'] }}</td>
            <td class="px-3 py-1 align-middle text-right font-medium text-gray-800 tabular-nums">{{ $row['in_progress'] }}</td>
            <td class="px-3 py-1 align-middle text-right font-semibold text-gray-900 tabular-nums">{{ $row['average'] }}%</td>
            <td class="px-3 py-1 align-middle">
              <a
                href="{{ route('santri.data.progres.detail', array_merge(['santriCode' => $row['code']], request()->only(['category', 'gender', 'q', 'page']))) }}"
                class="inline-flex items-center gap-1 rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100"
              >
                <i data-lucide="eye" class="h-3.5 w-3.5"></i>
                Detail
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-4 py-6 text-center text-sm text-gray-500">Belum ada data santri.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="flex shrink-0 items-center justify-between border-t border-gray-200 bg-white px-4 py-2" data-progress-pagination>
      @if($rows->onFirstPage())
        <button type="button" disabled class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-400 shadow-sm">
          <i data-lucide="arrow-left" class="h-3.5 w-3.5"></i>
          Previous
        </button>
      @else
        <a href="{{ $rows->previousPageUrl() }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
          <i data-lucide="arrow-left" class="h-3.5 w-3.5"></i>
          Previous
        </a>
      @endif

      <div class="flex items-center gap-1">
        @php
          $current = $rows->currentPage();
          $last = $rows->lastPage();
          $start = max(1, $current - 1);
          $end = min($last, $current + 1);
        @endphp

        @if($start > 1)
          <a href="{{ $rows->url(1) }}" class="inline-flex h-8 min-w-[32px] items-center justify-center rounded-lg px-2 text-xs font-medium text-gray-600 hover:bg-gray-50">1</a>
          @if($start > 2)
            <span class="px-1 text-xs text-gray-400">...</span>
          @endif
        @endif

        @for($page = $start; $page <= $end; $page++)
          <a href="{{ $rows->url($page) }}"
             class="inline-flex h-8 min-w-[32px] items-center justify-center rounded-lg px-2 text-xs font-medium {{ $page === $current ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50' }}">
            {{ $page }}
          </a>
        @endfor

        @if($end < $last)
          @if($end < $last - 1)
            <span class="px-1 text-xs text-gray-400">...</span>
          @endif
          <a href="{{ $rows->url($last) }}" class="inline-flex h-8 min-w-[32px] items-center justify-center rounded-lg px-2 text-xs font-medium text-gray-600 hover:bg-gray-50">{{ $last }}</a>
        @endif
      </div>

      @if($rows->hasMorePages())
        <a href="{{ $rows->nextPageUrl() }}" class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
          Next
          <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
        </a>
      @else
        <button type="button" disabled class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-400 shadow-sm">
          Next
          <i data-lucide="arrow-right" class="h-3.5 w-3.5"></i>
        </button>
      @endif
  </div>
</div>
