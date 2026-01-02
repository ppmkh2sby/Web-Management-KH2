@extends('layouts.santri-modern')
@section('title', 'Progress Keilmuan' . ($category === 'al-hadits' ? ' - Hadits' : ''))

@section('content')
@php
  $tabs = [
    ['key' => 'al-quran', 'label' => 'Al-Quran'],
    ['key' => 'al-hadits', 'label' => 'Al Hadits'],
  ];

  // Header card dinamis berdasarkan kategori
  $materiHeaderTitle = $category === 'al-hadits' ? 'Materi Al-Hadits' : 'Materi Al-Quran';
  $materiHeaderDesc  = 'Lorem ipsum dolor sit amet consectetur. Viverra venenatis in nunc nulla sit.';

  $pages = $modules->values()->chunk(6);
@endphp

<div class="w-full space-y-3.5">
  {{-- Header (breadcrumb + title + user button) --}}
  <div class="flex flex-wrap items-start justify-between gap-4">
    <div class="space-y-1.5">
      <div class="flex items-center gap-2 text-sm text-gray-500">
        <span>Dashboard</span>
        <span class="text-gray-300">/</span>
        <span class="font-medium text-emerald-700">Progress Keilmuan</span>
      </div>

      <div class="space-y-0.5">
        <h1 class="text-[20px] font-semibold text-gray-900">Progress Keilmuan</h1>
        <p class="text-xs text-gray-600">
          Lorem ipsum dolor sit amet consectetur. Volutpat tellus facilisi nulla commodo non tellus quis.
        </p>
      </div>
    </div>

    <button type="button"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm shadow-sm hover:bg-gray-50">
      <div class="h-6 w-6 rounded-full bg-gray-200 grid place-items-center text-[11px] font-semibold text-gray-700">
        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
      </div>
      <span class="text-sm font-medium text-gray-800">{{ auth()->user()->name ?? 'User' }}</span>
      <i data-lucide="chevron-down" class="w-4 h-4 text-gray-500"></i>
    </button>
  </div>

  {{-- Tabs --}}
  <div class="flex items-center gap-6 border-b border-gray-200">
    @foreach($tabs as $tab)
      @php $active = $category === $tab['key']; @endphp
      <a href="{{ route('santri.data.progres', ['category' => $tab['key']]) }}"
         class="pb-2.5 text-sm font-medium
                {{ $active ? 'text-emerald-700 border-b-2 border-emerald-600' : 'text-gray-500 hover:text-gray-700' }}">
        {{ $tab['label'] }}
      </a>
    @endforeach
  </div>

  {{-- Floating Alerts (tidak menggeser konten) --}}
  @if (session('success'))
    <div class="fixed top-4 right-4 z-50 max-w-md" x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2" x-init="setTimeout(() => show = false, 5000)">
      <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 flex items-start gap-3 shadow-lg">
        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5"></i>
        <div class="flex-1">
          <p class="text-sm font-semibold text-emerald-900">Berhasil!</p>
          <p class="text-sm text-emerald-800 mt-0.5">{{ session('success') }}</p>
        </div>
        <button @click="show = false" class="text-emerald-600 hover:text-emerald-800">
          <i data-lucide="x" class="w-4 h-4"></i>
        </button>
      </div>
    </div>
  @endif

  @if ($errors->any())
    <div class="fixed top-4 right-4 z-50 max-w-md" x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2">
      <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 flex items-start gap-3 shadow-lg">
        <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5"></i>
        <div class="flex-1">
          <p class="text-sm font-semibold text-red-900">Ada input yang perlu dicek ulang</p>
          <ul class="mt-1 list-disc space-y-1 pl-4 text-sm text-red-800">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
        </div>
        <button @click="show = false" class="text-red-600 hover:text-red-800">
          <i data-lucide="x" class="w-4 h-4"></i>
        </button>
      </div>
    </div>
  @endif

  {{-- Stats Cards + Update Terbaru + Table --}}
  <div class="grid grid-cols-1 lg:grid-cols-[1fr_minmax(270px,270px)] gap-4 items-start">
    {{-- Left Column: Stats Cards + Materi Al-Quran --}}
    <div class="space-y-4">
      {{-- 4 Stats Cards Horizontal --}}
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2">
        <div class="rounded-xl border border-gray-200 bg-white p-2 shadow-sm">
          <p class="text-[10px] font-medium text-gray-600">Total Materi</p>
          <p class="mt-1 text-xl font-semibold leading-7 text-gray-900">{{ $stats['total'] }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-2 shadow-sm">
          <p class="text-[10px] font-medium text-gray-600">Materi Selesai</p>
          <p class="mt-1 text-xl font-semibold leading-7 text-gray-900">{{ $stats['completed'] }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-2 shadow-sm">
          <p class="text-[10px] font-medium text-gray-600">Materi Dalam Pengerjaan</p>
          <p class="mt-1 text-xl font-semibold leading-7 text-gray-900">{{ $stats['inProgress'] }}</p>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-2 shadow-sm">
          <p class="text-[10px] font-medium text-gray-600">Rata-rata Pencapaian</p>
          <p class="mt-1 text-xl font-semibold leading-7 text-gray-900">{{ $stats['average'] }}%</p>
        </div>
      </div>

      {{-- Form progres (Materi Al-Quran Card) --}}
      <form method="POST" action="{{ route('santri.data.progres.sync') }}" 
            x-data="{ 
              page: 1, 
              total: {{ $pages->count() }},
              searchQuery: '',
              visiblePages: [],
              get isSearching() { return this.searchQuery.trim() !== ''; },
              normalizeText(text) {
                const lower = (text ?? '').toString().toLowerCase();
                return {
                  spaced: lower.replace(/\s+/g, ' ').trim(),
                  tight: lower.replace(/[^a-z0-9]/g, '')
                };
              },
              filterRows() {
                const queryRaw = (this.searchQuery ?? '').toLowerCase().trim();
                const queryNorm = this.normalizeText(queryRaw);
                const allPages = this.$el.querySelectorAll('[data-page-container]');
                this.visiblePages = [];
                
                allPages.forEach(pageEl => {
                  let hasMatch = false;
                  const rows = pageEl.querySelectorAll('[data-searchable]');
                  
                  rows.forEach(row => {
                    const text = row.getAttribute('data-searchable') || row.textContent || '';
                    const textNorm = this.normalizeText(text);
                    const match = !queryRaw
                      || textNorm.spaced.includes(queryNorm.spaced)
                      || textNorm.tight.includes(queryNorm.tight);
                    row.style.display = match ? '' : 'none';
                    hasMatch = hasMatch || match;
                  });

                  if (queryRaw && hasMatch) {
                    this.visiblePages.push(Number(pageEl.getAttribute('data-page-container')));
                  }
                });

                if (queryRaw && this.visiblePages.length) {
                  this.page = this.visiblePages[0];
                }
              }
            }"
            x-effect="filterRows()">
        @csrf
        <input type="hidden" name="category" value="{{ $category }}" />

        <div class="rounded-xl border border-gray-200 bg-white overflow-hidden shadow-sm">
          {{-- Card header --}}
          <div class="flex items-center justify-between gap-3 px-3 pt-2.5 pb-1.5">
            <div class="flex-shrink-0">
              <h3 class="text-sm font-semibold leading-5 text-gray-900">{{ $materiHeaderTitle }}</h3>
            </div>

            <div class="flex items-center gap-2.5 flex-shrink-0">
              <div class="relative w-44">
                <i data-lucide="search"
                   class="pointer-events-none absolute left-2.5 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-500"></i>
                <input type="search"
                       placeholder="Cari materi"
                       x-model.trim="searchQuery"
                       @input.debounce.100="filterRows()"
                       class="h-8 w-full rounded-lg border border-gray-300 bg-white pl-8 pr-2.5 text-xs text-gray-900 placeholder:text-gray-500
                              focus:border-emerald-600 focus:outline-none focus:ring-1 focus:ring-emerald-600/20 shadow-sm" />
              </div>

              <button type="submit"
                      class="inline-flex h-8 items-center gap-1 rounded-lg bg-emerald-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 shadow-sm border-2 border-white/[0.12] whitespace-nowrap">
                <i data-lucide="save" class="w-3.5 h-3.5"></i>
                Simpan Progress
              </button>
            </div>
          </div>

          <div class="h-px bg-gray-200 mt-2.5"></div>
        <div class="grid grid-cols-[165px_145px_1fr] items-center bg-gray-50 px-3 py-1.5 text-[10px] font-semibold uppercase text-gray-600 border-b border-gray-200">
          <div class="flex items-center gap-0.5">
            {{ $category === 'al-quran' ? 'Juz' : 'Hadits' }}
            <i data-lucide="chevrons-up-down" class="w-2.5 h-2.5 text-gray-500"></i>
          </div>
          <div class="flex items-center gap-0.5">
            Progress Halaman
            <i data-lucide="info" class="w-2.5 h-2.5 text-gray-500"></i>
          </div>
          <div class="flex items-center gap-0.5">
            Persentase
            <i data-lucide="info" class="w-2.5 h-2.5 text-gray-500"></i>
          </div>
        </div>

        @foreach($pages as $pageIndex => $pageModules)
          <div 
            x-show="(!isSearching && page === {{ $pageIndex + 1 }}) || (isSearching && visiblePages.includes({{ $pageIndex + 1 }}))"
            data-page-container="{{ $pageIndex + 1 }}" 
            x-cloak 
            class="divide-y divide-gray-200">
            @foreach($pageModules as $loopIndex => $module)
              @php
                $index = ($pageIndex * 6) + $loopIndex;
                $title = $module['judul'] ?? $module[0] ?? '';
                $target = $module['target'] ?? null;

                $row = $items[$title] ?? ['value' => null, 'persentase' => 0];
                $currentValue = old('modules.' . $index . '.value', $row['value'] ?? null);

                $percent = min((int) ($row['persentase'] ?? 0), 100);

                $maxValue = $target;
                $exceeds = $maxValue !== null && $currentValue !== null && $currentValue !== '' && (int) $currentValue > $maxValue;

                $barColor = $percent > 0 ? 'bg-emerald-600' : 'bg-gray-300';
              @endphp

              <div class="grid grid-cols-[165px_145px_1fr] items-center px-3.5 py-2 border-b border-gray-200 hover:bg-gray-50" data-searchable="{{ $title }}">
                {{-- Title --}}
                <div class="font-medium text-[11px] leading-4 text-gray-900">
                  {{ $title }}
                </div>

                {{-- Input --}}
                <div class="pr-2.5">
                  <input
                    type="number"
                    name="modules[{{ $index }}][value]"
                    min="0"
                    @if($maxValue) max="{{ $maxValue }}" @endif
                    value="{{ $currentValue !== null ? $currentValue : '' }}"
                    placeholder="{{ $maxValue ? 'Maks: '.$maxValue : '0' }}"
                    class="h-7 w-full rounded-lg border px-2 text-xs text-gray-900 placeholder:text-gray-500 shadow-sm
                      {{ $exceeds
                        ? 'border-red-300 text-red-700 focus:border-red-400 focus:ring-1 focus:ring-red-400/20'
                        : 'border-gray-300 focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600/20'
                      }}"
                  />
                  <input type="hidden" name="modules[{{ $index }}][judul]" value="{{ $title }}" />
                </div>

                {{-- Progress Bar + Percent --}}
                <div class="flex items-center gap-2 pr-3">
                  <div class="flex-1 h-1 rounded-full bg-gray-200">
                    <div class="h-full rounded-full {{ $barColor }} transition-all duration-300" style="width: {{ $percent }}%"></div>
                  </div>
                  <div class="text-[11px] font-medium leading-4 text-gray-700 whitespace-nowrap min-w-[30px] text-right">
                    {{ $percent }}%
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        @endforeach

        {{-- Pagination --}}
        @if($pages->count() > 1)
          <div x-show="!isSearching" class="flex items-center justify-between border-t border-gray-200 bg-white px-2.5 py-1.5">
            {{-- Previous Button --}}
            <button type="button"
                    @click="if(page > 1) page -= 1"
                    :disabled="page === 1"
                    class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2 py-1 text-[11px] font-semibold text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
              <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
              Previous
            </button>

            {{-- Page Numbers --}}
            <div class="flex items-center gap-0.5">
              @php $totalPages = $pages->count(); @endphp

              {{-- Page numbers like: 1 2 3 ... 8 9 10 --}}
              @if($totalPages <= 7)
                @for($i = 1; $i <= $totalPages; $i++)
                  <button type="button"
                          @click="page = {{ $i }}"
                          class="h-7 w-7 rounded-lg font-medium text-[11px] transition-colors"
                          :class="page === {{ $i }} ? 'bg-gray-50 text-gray-900' : 'text-gray-600 hover:bg-gray-50'">
                    {{ $i }}
                  </button>
                @endfor
              @else
                @for($i = 1; $i <= 3; $i++)
                  <button type="button"
                          @click="page = {{ $i }}"
                          class="h-7 w-7 rounded-lg font-medium text-[11px] transition-colors"
                          :class="page === {{ $i }} ? 'bg-gray-50 text-gray-900' : 'text-gray-600 hover:bg-gray-50'">
                    {{ $i }}
                  </button>
                @endfor

                <span class="px-1 text-gray-600 font-medium text-[11px]">…</span>

                @for($i = $totalPages - 2; $i <= $totalPages; $i++)
                  <button type="button"
                          @click="page = {{ $i }}"
                          class="h-7 w-7 rounded-lg font-medium text-[11px] transition-colors"
                          :class="page === {{ $i }} ? 'bg-gray-50 text-gray-900' : 'text-gray-600 hover:bg-gray-50'">
                    {{ $i }}
                  </button>
                @endfor
              @endif
            </div>

            {{-- Next Button --}}
            <button type="button"
                    @click="if(page < total) page += 1"
                    :disabled="page === total"
                    class="inline-flex items-center gap-1 rounded-lg border border-gray-300 bg-white px-2 py-1 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm">
              Next
              <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </button>
          </div>
        @endif
        </div>
      </form>
    </div>

    {{-- Right Column: Update Terbaru Panel --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden flex flex-col self-stretch">
      <div class="flex items-center gap-2.5 px-2.5 py-2 border-b border-gray-200">
        <h3 class="flex-1 text-xs font-semibold leading-4 text-gray-900">Update Terbaru</h3>
        <span class="rounded-full border border-gray-200 bg-gray-50 px-2 py-0.5 text-[10px] font-medium text-gray-700">
          {{ $recentUpdates->count() }} Catatan
        </span>
      </div>

      <div class="px-2.5 py-0 flex-1 overflow-y-auto">
        @if($recentUpdates->isEmpty())
          <p class="py-2.5 text-[11px] text-gray-500">Belum ada catatan progres di kategori ini.</p>
        @else
          <ul class="divide-y divide-gray-100">
            @foreach($recentUpdates as $entry)
              <li class="py-2 first:pt-2.5">
                <div class="flex items-start justify-between gap-2 mb-0.5">
                  <p class="text-xs font-semibold leading-4 text-gray-900">{{ $entry->judul }}</p>
                  <p class="text-[10px] text-gray-600 whitespace-nowrap">
                    {{ optional($entry->terakhir_setor ?? $entry->updated_at)->translatedFormat('H:i \\W\\I\\B, d M Y') ?? '-' }}
                  </p>
                </div>
                <p class="text-[11px] font-medium leading-4 text-gray-600">
                  {{ $entry->capaian }}/{{ $entry->target }} {{ $entry->satuan }}@if(isset($entry->persentase)) · {{ $entry->persentase }}%@endif
                </p>
              </li>
            @endforeach
          </ul>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
