@php
  $activeSummary = $activeTab === 'putri' ? $putriSummary : $putraSummary;
  $activeLabel = $activeTab === 'putri' ? 'Putri' : 'Putra';
@endphp

<div class="rekap-layout grid grid-cols-1 gap-4 flex-1 min-h-0">
  <aside class="rekap-left md:h-full md:min-h-0 md:pr-1">
    <div class="rekap-card h-full p-3.5">
      <div class="flex items-center justify-between gap-2">
        <h2 class="text-sm font-semibold text-gray-900">Panel Rekap</h2>
        <div class="inline-flex items-center rounded-lg border border-gray-200 bg-gray-50 p-1">
          <button type="button"
                  data-rekap-tab="putra"
                  class="rekap-tab-btn inline-flex min-w-[58px] items-center justify-center rounded-md px-2.5 py-1.5 text-xs font-semibold transition {{ $activeTab === 'putra' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-700 hover:bg-white' }}">
            Putra
          </button>
          <button type="button"
                  data-rekap-tab="putri"
                  class="rekap-tab-btn inline-flex min-w-[58px] items-center justify-center rounded-md px-2.5 py-1.5 text-xs font-semibold transition {{ $activeTab === 'putri' ? 'bg-emerald-600 text-white shadow-sm' : 'text-gray-700 hover:bg-white' }}">
            Putri
          </button>
        </div>
      </div>

      <div class="mt-2 space-y-1.5">
        <div class="rounded-xl border border-slate-200 bg-slate-50/70 p-2">
          <div class="flex items-center justify-between gap-2">
            <p class="text-xs font-semibold text-gray-900">Ringkasan {{ $activeLabel }}</p>
            <span class="rekap-summary-chip border-emerald-200 bg-emerald-50 text-emerald-700">{{ $activeSummary['persentase'] }}% hadir</span>
          </div>
          <div class="mt-1.5 grid grid-cols-4 gap-1.5">
            <div class="rounded-lg border border-slate-200 bg-white px-2 py-1.5">
              <p class="text-[10px] leading-3 text-gray-500">Santri</p>
              <p class="text-sm font-semibold text-gray-900 leading-5">{{ $activeSummary['total_santri'] }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white px-2 py-1.5">
              <p class="text-[10px] leading-3 text-gray-500">Sesi</p>
              <p class="text-sm font-semibold text-gray-900 leading-5">{{ $activeSummary['total_sesi'] }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white px-2 py-1.5">
              <p class="text-[10px] leading-3 text-gray-500">Input</p>
              <p class="text-sm font-semibold text-gray-900 leading-5">{{ $activeSummary['total_input'] }}</p>
            </div>
            <div class="rounded-lg border border-slate-200 bg-white px-2 py-1.5">
              <p class="text-[10px] leading-3 text-gray-500">% Hadir</p>
              <p class="text-sm font-semibold text-emerald-700 leading-5">{{ $activeSummary['persentase'] }}%</p>
            </div>
          </div>
          <div class="mt-1.5 flex flex-wrap gap-1">
            <span class="rekap-summary-chip border-emerald-200 bg-emerald-50 text-emerald-700">Hadir: {{ $activeSummary['hadir'] }}</span>
            <span class="rekap-summary-chip border-blue-200 bg-blue-50 text-blue-700">Izin: {{ $activeSummary['izin'] }}</span>
            <span class="rekap-summary-chip border-amber-200 bg-amber-50 text-amber-700">Sakit: {{ $activeSummary['sakit'] }}</span>
            <span class="rekap-summary-chip border-rose-200 bg-rose-50 text-rose-700">Alpha: {{ $activeSummary['alpha'] }}</span>
          </div>
        </div>
      </div>

      <div class="mt-3 border-t border-gray-200 pt-3">
        <h3 class="text-sm font-semibold text-gray-900 mb-2">Filter Rekap</h3>
        <form id="rekap-filter-form" method="GET" action="{{ route('santri.presensi.rekap') }}" class="space-y-2">
          <input type="hidden" name="tab" id="rekap-tab-input" value="{{ $activeTab }}" />
          <input type="hidden" name="page" value="{{ method_exists($activeRows, 'currentPage') ? $activeRows->currentPage() : 1 }}" id="rekap-page-input" />

          <div class="grid grid-cols-1 gap-2 md:grid-cols-2">
            <div class="space-y-1">
              <label class="block text-[11px] font-semibold uppercase tracking-wide text-gray-600">Bulan</label>
              <input type="month"
                     name="bulan"
                     value="{{ $bulanInput }}"
                     class="h-9 w-full rounded-lg border border-gray-300 bg-white px-2.5 text-sm text-gray-900 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600"
                     required />
            </div>

            <div class="space-y-1">
              <label class="block text-[11px] font-semibold uppercase tracking-wide text-gray-600">Kategori</label>
              <select name="kategori" class="h-9 w-full rounded-lg border border-gray-300 bg-white px-2.5 text-sm text-gray-900 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
                <option value="all" @selected($selectedKategori === 'all')>Semua Kategori</option>
                @foreach($kategoriOptions as $kategori)
                  <option value="{{ $kategori }}" @selected($selectedKategori === $kategori)>{{ ucfirst($kategori) }}</option>
                @endforeach
              </select>
            </div>

            <div class="space-y-1 md:col-span-2">
              <label class="block text-[11px] font-semibold uppercase tracking-wide text-gray-600">Waktu</label>
              <select name="waktu" class="h-9 w-full rounded-lg border border-gray-300 bg-white px-2.5 text-sm text-gray-900 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
                <option value="all" @selected($selectedWaktu === 'all')>Semua Waktu</option>
                @foreach($waktuOptions as $waktu)
                  <option value="{{ $waktu }}" @selected($selectedWaktu === $waktu)>{{ ucfirst($waktu) }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-2 pt-0.5">
            <button type="submit" class="inline-flex h-9 items-center justify-center rounded-lg bg-emerald-600 px-3 text-xs font-semibold text-white hover:bg-emerald-700">
              Tampilkan
            </button>
            <a href="{{ route('santri.presensi.rekap', ['tab' => $activeTab]) }}" wire:navigate
               class="inline-flex h-9 items-center justify-center rounded-lg border border-gray-300 bg-white px-3 text-xs font-medium text-gray-700 hover:bg-gray-50">
              Reset
            </a>
          </div>
        </form>
      </div>
    </div>
  </aside>

  <section class="rekap-right rekap-card overflow-hidden min-h-[420px] md:min-h-0 md:h-full md:flex md:flex-col">
    <div class="border-b border-gray-200 px-4 py-3">
      <h3 class="text-sm font-semibold text-gray-900">Detail Data Rekap {{ $activeLabel }}</h3>
    </div>

    <div class="rekap-table-panel flex-1 min-h-0 flex flex-col">
      <div class="rekap-table-scroll elegant-scroll h-full max-h-full overflow-auto">
        <table class="w-full text-sm">
          <thead class="sticky top-0 z-10 bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-3 py-3 text-left text-[11px] font-semibold text-gray-600 uppercase tracking-wide">Nama</th>
              <th class="px-3 py-3 text-left text-[11px] font-semibold text-gray-600 uppercase tracking-wide">Tim</th>
              <th class="px-3 py-3 text-right text-[11px] font-semibold text-gray-600 uppercase tracking-wide">Total</th>
              <th class="px-3 py-3 text-right text-[11px] font-semibold text-gray-600 uppercase tracking-wide">H</th>
              <th class="px-3 py-3 text-right text-[11px] font-semibold text-gray-600 uppercase tracking-wide">I</th>
              <th class="px-3 py-3 text-right text-[11px] font-semibold text-gray-600 uppercase tracking-wide">S</th>
              <th class="px-3 py-3 text-right text-[11px] font-semibold text-gray-600 uppercase tracking-wide">A</th>
              <th class="px-3 py-3 text-right text-[11px] font-semibold text-gray-600 uppercase tracking-wide">% Hadir</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @forelse($activeRows as $row)
              <tr class="hover:bg-gray-50">
                <td class="px-3 py-3 text-xs font-semibold text-gray-900">{{ $row['nama_lengkap'] }}</td>
                <td class="px-3 py-3 text-xs text-gray-600">{{ $row['tim'] }}</td>
                <td class="px-3 py-3 text-xs text-right text-gray-700">{{ $row['total_input'] }}</td>
                <td class="px-3 py-3 text-xs text-right text-emerald-700 font-medium">{{ $row['hadir'] }}</td>
                <td class="px-3 py-3 text-xs text-right text-blue-700 font-medium">{{ $row['izin'] }}</td>
                <td class="px-3 py-3 text-xs text-right text-amber-700 font-medium">{{ $row['sakit'] }}</td>
                <td class="px-3 py-3 text-xs text-right text-rose-700 font-medium">{{ $row['alpha'] }}</td>
                <td class="px-3 py-3 text-xs text-right font-semibold text-gray-900">{{ $row['persentase'] }}%</td>
              </tr>
            @empty
              <tr>
                <td colspan="8" class="px-3 py-10 text-center text-xs text-gray-500">Belum ada data presensi untuk filter ini.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    @if(method_exists($activeRows, 'hasPages') && $activeRows->hasPages())
      <div class="border-t border-gray-200 px-4 py-3 text-sm" data-rekap-pagination>
        {{ $activeRows->links() }}
      </div>
    @endif
  </section>
</div>
