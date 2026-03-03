@extends('layouts.santri-modern')
@section('title', 'Input Kafarah')

@section('content')
<div class="space-y-3.5">
  @if(session('success'))
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 text-sm">
      {{ session('success') }}
    </div>
  @endif
  @if ($errors->any())
    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-800 text-sm space-y-1">
      @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
      @endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('santri.kafarah.store') }}">
    @csrf
    <div class="grid grid-cols-1 xl:grid-cols-[minmax(0,1fr)_360px] gap-6">
      {{-- Main Content: Santri List --}}
      <div class="space-y-5">
        {{-- Header --}}
        <div class="space-y-2">
          <nav class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('santri.dashboard') }}" wire:navigate class="hover:text-gray-800">Dashboard</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400"></i>
            <span>Kafarah Santri</span>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400"></i>
            <span class="text-emerald-700 font-medium">Input Kafarah Santri</span>
          </nav>

          <div class="flex items-start justify-between gap-4">
            <div>
              <h1 class="text-[22px] font-semibold text-gray-900">Input Kafarah Santri</h1>
              <p class="text-sm text-gray-600 max-w-3xl mt-1">Input data kafarah untuk santri</p>
            </div>
            <div class="flex items-center gap-2">
              @foreach(['putra'=>'Putra','putri'=>'Putri'] as $val => $label)
                <a href="{{ route('santri.kafarah.create', ['gender' => $val]) }}" wire:navigate
                   class="inline-flex items-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium {{ $gender === $val ? 'bg-emerald-600 text-white border-emerald-600' : 'border-gray-200 text-gray-700 hover:border-emerald-300' }}">
                  {{ $label }}
                </a>
              @endforeach
            </div>
          </div>
        </div>

        {{-- Santri Table --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
          <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
            <h2 class="text-base font-semibold text-gray-900">Santri</h2>
            <div class="relative w-72">
              <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
              <input type="text"
                     id="search-santri"
                     placeholder="Cari santri"
                     class="w-full rounded-lg border border-gray-300 bg-white pl-9 pr-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-500 focus:ring-1 focus:ring-emerald-600/20 focus:border-emerald-600" />
            </div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="bg-gray-50 border-b border-gray-200">
                  <th class="px-5 py-3 text-left">
                    <div class="flex items-center gap-3 text-sm font-semibold text-gray-700">
                      <input type="checkbox"
                             class="h-5 w-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 accent-emerald-600"
                             id="select-all" />
                      <label for="select-all" class="cursor-pointer">Nama</label>
                    </div>
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                @forelse($santriList as $santri)
                  <tr class="hover:bg-gray-50 transition-colors santri-row">
                    <td class="px-5 py-3">
                      <div class="flex items-center gap-3">
                        <input type="checkbox"
                               name="santri_ids[]"
                               class="h-5 w-5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 accent-emerald-600 santri-checkbox"
                               value="{{ $santri->id }}"
                               data-santri-name="{{ $santri->nama_lengkap }}" />
                        <span class="text-xs font-semibold text-gray-900 leading-5">{{ $santri->nama_lengkap }}</span>
                      </div>
                    </td>
                  </tr>
                @empty
                  <tr>
                    <td class="px-6 py-12 text-center text-gray-500 text-sm">
                      Tidak ada data santri.
                    </td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          {{-- Pagination --}}
          @if($santriList->hasPages())
            <div class="border-t border-gray-200 px-3 py-2.5 flex items-center justify-between">
              {{ $santriList->links() }}
            </div>
          @endif
        </div>
      </div>

      {{-- Right Side: Input Form --}}
      <div class="mt-6 xl:mt-0 xl:sticky xl:top-4 xl:self-start">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-4 space-y-4 xl:max-h-[calc(100vh-6rem)] xl:overflow-y-auto">
          {{-- Stats Cards --}}
          <div class="rounded-xl border border-gray-200 bg-white px-3 py-3 text-center">
            <p class="text-xs font-medium text-gray-600 mb-1.5">Santri Dipilih</p>
            <p class="text-2xl leading-[30px] font-semibold text-gray-900" id="stat-selected">{{ $stats['selected'] }}</p>
          </div>

          {{-- Form Inputs --}}
          <div class="space-y-3.5">
            {{-- Tanggal --}}
            <div>
              <label for="tanggal-input" class="block text-xs font-semibold text-gray-700 mb-1.5 leading-tight">Tanggal</label>
              <div class="relative">
                <i data-lucide="calendar" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                <input type="date"
                       id="tanggal-input"
                       name="tanggal"
                       value="{{ old('tanggal', now()->format('Y-m-d')) }}"
                       required
                       class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-3.5 py-2.5 text-sm text-gray-900 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600" />
              </div>
            </div>

            {{-- Jenis Pelanggaran (global) --}}
            <div>
              <label for="jenis-pelanggaran" class="block text-xs font-semibold text-gray-700 mb-1.5 leading-tight">Jenis Pelanggaran</label>
              <select id="jenis-pelanggaran"
                      name="jenis_pelanggaran"
                      required
                      class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 focus:ring-2 focus:ring-emerald-600/20 focus:border-emerald-600">
                <option value="">Pilih jenis pelanggaran</option>
                @foreach($jenisPelanggaranOptions as $key => $label)
                  <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
              </select>
              <p class="text-[11px] text-gray-500 mt-1">Tenggat otomatis H+7 dari tanggal kafarah.</p>
            </div>
          </div>

          {{-- Submit Button --}}
          <div class="pt-3 border-t border-gray-200">
            <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-600 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm transition-colors">
              <i data-lucide="check" class="w-4 h-4"></i>
              Submit Kafarah
            </button>
          </div>
        </div>
      </div>
    </div>
  </form>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const statSelected = document.getElementById('stat-selected');

    // Update stats when checkbox changes
    document.querySelectorAll('.santri-checkbox').forEach(checkbox => {
      checkbox.addEventListener('change', updateStats);
    });

    function updateStats() {
      const selected = document.querySelectorAll('.santri-checkbox:checked').length;
      statSelected.textContent = selected;
    }

    // Select all functionality
    document.getElementById('select-all')?.addEventListener('change', function() {
      document.querySelectorAll('.santri-checkbox').forEach(cb => {
        cb.checked = this.checked;
      });
      updateStats();
    });

    // Search santri
    const searchInput = document.getElementById('search-santri');
    if (searchInput) {
      searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('.santri-row').forEach(row => {
          const santriName = row.querySelector('.santri-checkbox').dataset.santriName.toLowerCase();
          if (santriName.includes(searchTerm)) {
            row.style.display = '';
          } else {
            row.style.display = 'none';
          }
        });
      });
    }
  });
</script>
@endsection
