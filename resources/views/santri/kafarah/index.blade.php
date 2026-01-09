@extends('layouts.santri-modern')
@section('title', $mode === 'team' ? 'Kafarah Santri' : 'Kafarah Saya')

@section('content')
<div class="space-y-3.5">
  @if(session('success'))
    <div class="pointer-events-none fixed inset-x-0 top-4 z-50 flex justify-center px-4">
      <div class="toast-banner relative w-full max-w-3xl" data-autohide="true">
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-3 text-emerald-800 text-sm shadow-md shadow-emerald-100/70">
          {{ session('success') }}
        </div>
      </div>
    </div>
  @endif
  @if ($errors->any())
    <div class="pointer-events-none fixed inset-x-0 top-4 z-50 flex justify-center px-4">
      <div class="toast-banner relative w-full max-w-3xl" data-autohide="true">
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-5 py-3 text-rose-800 text-sm shadow-md shadow-rose-100/70 space-y-1">
          @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
          @endforeach
        </div>
      </div>
    </div>
  @endif

  @if($mode === 'team')
  {{-- Kafarah Santri View --}}
  <div class="space-y-3.5">
    {{-- Page Header --}}
    <div class="space-y-2">
      <div class="text-xs text-gray-500">
        <span>Dashboard</span>
        <span class="mx-2">›</span>
        <span class="text-gray-900">Kafarah Santri</span>
      </div>
      <div class="flex items-start justify-between gap-4">
        <div>
          <h1 class="text-[20px] font-semibold text-gray-900">Kafarah Santri</h1>
          <p class="text-[10px] text-gray-600 mt-0.5">Data kafarah seluruh santri</p>
        </div>
        <div class="flex items-center gap-2">
          <div class="relative">
            <form method="GET" action="{{ route('santri.kafarah.index') }}">
              <input type="hidden" name="mode" value="{{ $mode }}" />
              <i data-lucide="search" class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2"></i>
              <input id="search-input" name="search" value="{{ $search }}" placeholder="Cari santri" 
                     class="rounded-lg border border-gray-200 bg-white pl-8 pr-3 py-2 text-sm placeholder:text-gray-400 focus:ring-1 focus:ring-emerald-600/20 focus:border-emerald-600 transition-all w-72" />
            </form>
          </div>
          @if($canManage)
            <a href="{{ route('santri.kafarah.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3.5 py-2 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm">
              <i data-lucide="plus" class="w-4 h-4"></i>
              Input Kafarah
            </a>
          @endif
        </div>
      </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-gray-50 border-b border-gray-200">
              <th class="px-3 py-1.5 text-left">
                <div class="flex items-center gap-0.5 text-[9px] font-semibold text-gray-600 uppercase">
                  <span>Nama</span>
                </div>
              </th>
              <th class="px-3 py-1.5 text-left">
                <div class="flex items-center gap-0.5 text-[9px] font-semibold text-gray-600 uppercase">
                  <span>Tim</span>
                </div>
              </th>
              <th class="px-3 py-1.5 text-left">
                <div class="flex items-center gap-0.5 text-[9px] font-semibold text-gray-600 uppercase">
                  <span>Tanggal</span>
                </div>
              </th>
              <th class="px-3 py-1.5 text-left">
                <div class="flex items-center gap-0.5 text-[9px] font-semibold text-gray-600 uppercase">
                  <span>Jenis Pelanggaran</span>
                </div>
              </th>
              <th class="px-3 py-1.5 text-left">
                <div class="flex items-center gap-0.5 text-[9px] font-semibold text-gray-600 uppercase">
                  <span>Kafarah</span>
                </div>
              </th>
              <th class="px-3 py-1.5 text-left">
                <div class="flex items-center gap-0.5 text-[9px] font-semibold text-gray-600 uppercase">
                  <span>Tenggat</span>
                </div>
              </th>
              @if($canManage)
              <th class="px-3 py-1.5 text-center">
                <div class="flex items-center justify-center gap-0.5 text-[9px] font-semibold text-gray-600 uppercase">
                  <span>Aksi</span>
                </div>
              </th>
              @endif
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-100">
            @forelse($kafarahs as $row)
              <tr class="hover:bg-gray-50">
                <td class="px-3 py-2.5">
                  <span class="text-xs font-medium text-gray-900">{{ $row->santri->nama_lengkap ?? '-' }}</span>
                </td>
                <td class="px-3 py-2.5">
                  <span class="text-xs text-gray-600">{{ $row->santri->tim ?? '-' }}</span>
                </td>
                <td class="px-3 py-2.5">
                  <span class="text-xs text-gray-600">{{ $row->tanggal->format('d M Y') }}</span>
                </td>
                <td class="px-3 py-2.5">
                  <span class="text-xs text-gray-600">{{ $row->jenisPelanggaranLabel ?? '-' }}</span>
                </td>
                <td class="px-3 py-2.5">
                  <span class="text-xs font-medium text-gray-900">{{ $row->kafarah ?? '-' }}</span>
                </td>
                <td class="px-3 py-2.5">
                  <span class="text-xs text-gray-600">{{ $row->tenggat ?? '-' }}</span>
                </td>
                @if($canManage)
                <td class="px-3 py-2.5 text-center">
                  <button type="button" 
                          class="action-menu-button inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50"
                          data-id="{{ $row->id }}"
                          data-santri-id="{{ $row->santri_id }}"
                          data-tanggal="{{ $row->tanggal->format('Y-m-d') }}"
                          data-jenis-pelanggaran="{{ $row->jenis_pelanggaran }}"
                          data-kafarah="{{ $row->kafarah }}"
                          data-jumlah-setor="{{ $row->jumlah_setor }}"
                          data-tanggungan="{{ $row->tanggungan }}"
                          data-tenggat="{{ $row->tenggat }}">
                    <i data-lucide="pencil" class="w-3 h-3"></i>
                    Edit
                  </button>
                </td>
                @endif
              </tr>
            @empty
              <tr>
                <td colspan="{{ $canManage ? 7 : 6 }}" class="px-4 py-12 text-center">
                  <div class="flex flex-col items-center gap-1.5">
                    <i data-lucide="inbox" class="w-10 h-10 text-gray-300"></i>
                    <p class="text-xs font-medium text-gray-500">Belum ada data kafarah.</p>
                  </div>
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      {{-- Pagination --}}
      @if($kafarahs->hasPages())
        <div class="border-t border-gray-200 px-4 py-3 flex items-center justify-between bg-gray-50">
          {{ $kafarahs->links() }}
        </div>
      @endif
    </div>
  </div>
  @endif

  @if($mode === 'mine')
  {{-- Kafarah Saya View --}}
  <div class="space-y-6">
    {{-- Breadcrumb --}}
    <div class="text-sm text-gray-500">
      <span>Dashboard</span>
      <span class="mx-2">›</span>
      <span class="text-gray-900">Kafarah Saya</span>
    </div>

    {{-- Page Header --}}
    <div>
      <h1 class="text-2xl font-semibold text-gray-900 leading-8">Kafarah Saya</h1>
      <p class="text-xs text-gray-600 mt-1 leading-relaxed">Data kafarah Anda</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-4 gap-3">
      {{-- Total Pelanggaran --}}
      <div class="bg-white border border-gray-200 rounded-xl px-4 py-3.5 shadow-sm">
        <p class="text-xs font-medium text-gray-600 leading-tight">Total Pelanggaran</p>
        <p class="text-2xl font-semibold text-gray-900 leading-8 mt-1.5">{{ $stats['total'] }}</p>
      </div>

      {{-- Total Kafarah --}}
      <div class="bg-white border border-gray-200 rounded-xl px-4 py-3.5 shadow-sm">
        <p class="text-xs font-medium text-gray-600 leading-tight">Total Kafarah</p>
        <p class="text-2xl font-semibold text-gray-900 leading-8 mt-1.5">{{ $stats['total_kafarah'] }}</p>
      </div>

      {{-- Jumlah Setor --}}
      <div class="bg-white border border-gray-200 rounded-xl px-4 py-3.5 shadow-sm">
        <p class="text-xs font-medium text-gray-600 leading-tight">Jumlah Setor</p>
        <p class="text-2xl font-semibold text-gray-900 leading-8 mt-1.5">{{ $stats['jumlah_setor'] }}</p>
      </div>

      {{-- Tanggungan Kafarah --}}
      <div class="bg-white border border-gray-200 rounded-xl px-4 py-3.5 shadow-sm">
        <p class="text-xs font-medium text-gray-600 leading-tight">Tanggungan Kafarah</p>
        <p class="text-2xl font-semibold text-gray-900 leading-8 mt-1.5">{{ $stats['tanggungan'] }}</p>
      </div>
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-[1fr_375px] gap-4">
      {{-- Table Section --}}
      <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        {{-- Table Header --}}
        <div class="border-b border-gray-200 px-4 py-3.5">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-base font-semibold text-gray-900 leading-6">Riwayat Keseluruhan Kafarah</h2>
            </div>
          </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-600 uppercase tracking-wide">
                  Tanggal
                </th>
                <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-600 uppercase tracking-wide">
                  Jenis Pelanggaran
                </th>
                <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-600 uppercase tracking-wide">
                  Kafarah
                </th>
                <th class="px-4 py-2.5 text-left text-[10px] font-semibold text-gray-600 uppercase tracking-wide">
                  Tenggat
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              @forelse($kafarahs as $row)
                <tr class="hover:bg-gray-50 transition-colors">
                  <td class="px-4 py-3 text-xs font-medium text-gray-900">
                    {{ $row->tanggal->translatedFormat('d M Y') }}
                  </td>
                  <td class="px-4 py-3 text-xs text-gray-600">
                    {{ $row->jenisPelanggaranLabel ?? '-' }}
                  </td>
                  <td class="px-4 py-3 text-xs font-medium text-gray-900">
                    {{ $row->kafarah ?? '-' }}
                  </td>
                  <td class="px-4 py-3 text-xs text-gray-600">
                    {{ $row->tenggat ?? '-' }}
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="px-4 py-12 text-center">
                    <div class="flex flex-col items-center gap-1.5">
                      <i data-lucide="inbox" class="w-10 h-10 text-gray-300"></i>
                      <p class="text-xs font-medium text-gray-500">Belum ada data kafarah.</p>
                    </div>
                  </td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- Pagination --}}
        @if($kafarahs->hasPages())
          <div class="border-t border-gray-200 px-4 py-3 flex items-center justify-between bg-gray-50">
            {{ $kafarahs->links() }}
          </div>
        @endif
      </div>

      {{-- Latest Updates Sidebar --}}
      <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden h-fit">
        {{-- Header --}}
        <div class="border-b border-gray-200 px-3.5 py-3">
          <h3 class="text-base font-semibold text-gray-900 leading-6">Update Terbaru</h3>
        </div>

        {{-- Updates List --}}
        <div class="divide-y divide-gray-200">
          @forelse($latestUpdates as $update)
            <div class="px-3.5 py-3">
              <div class="flex items-start justify-between gap-2 mb-1">
                <p class="text-sm font-semibold text-gray-900 flex-1 leading-5">
                  {{ $update->jenisPelanggaranLabel ?? 'Kafarah' }}
                </p>
                <span class="text-xs font-medium text-gray-600">
                  {{ $update->kafarah ?? '-' }}
                </span>
              </div>
              <p class="text-xs font-medium text-gray-500 leading-tight">
                {{ $update->tanggal->translatedFormat('l, d F Y') }}
              </p>
            </div>
          @empty
            <div class="px-3.5 py-10 text-center">
              <div class="flex flex-col items-center gap-1.5">
                <i data-lucide="clock" class="w-8 h-8 text-gray-300"></i>
                <p class="text-xs font-medium text-gray-500">Belum ada update terbaru.</p>
              </div>
            </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
  @endif
</div>

{{-- Modal edit/hapus kafarah --}}
@if($mode === 'team' && $canManage)
<div id="edit-modal" class="fixed inset-0 bg-black/40 z-40 hidden items-center justify-center">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6 space-y-4">
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-semibold text-gray-900">Edit Kafarah</h3>
      <button type="button" id="close-edit-modal" class="text-gray-500 hover:text-gray-700">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    <form id="edit-form" method="POST">
      @csrf
      @method('PATCH')
      <div class="space-y-3">
        <div>
          <label class="text-sm font-medium text-gray-700">Tanggal</label>
          <input type="date" name="tanggal" id="edit-tanggal" class="w-full rounded-lg border-gray-300 text-sm" required>
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Jenis Pelanggaran</label>
          <select name="jenis_pelanggaran" id="edit-jenis-pelanggaran" class="w-full rounded-lg border-gray-300 text-sm" required>
            @foreach($jenisPelanggaranOptions as $key => $label)
              <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Kafarah</label>
          <input type="text" name="kafarah" id="edit-kafarah" class="w-full rounded-lg border-gray-300 text-sm bg-gray-50" readonly>
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Jumlah Setor (Opsional)</label>
          <input type="number" name="jumlah_setor" id="edit-jumlah-setor" class="w-full rounded-lg border-gray-300 text-sm" min="0">
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Tenggat (Opsional)</label>
          <textarea name="tenggat" id="edit-tenggat" class="w-full rounded-lg border-gray-300 text-sm" rows="3"></textarea>
        </div>
      </div>
      <div class="mt-4 flex justify-end gap-2">
        <button type="button" id="cancel-edit" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Batal</button>
        <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">Simpan</button>
      </div>
    </form>
    <form id="delete-form" method="POST" class="hidden">
      @csrf
      @method('DELETE')
    </form>
    <div class="mt-2">
      <button type="button" id="delete-button" class="text-sm text-red-600 hover:text-red-700">Hapus Kafarah</button>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const editModal = document.getElementById('edit-modal');
    const closeEditModal = document.getElementById('close-edit-modal');
    const cancelEdit = document.getElementById('cancel-edit');
    const editForm = document.getElementById('edit-form');
    const deleteForm = document.getElementById('delete-form');
    const deleteButton = document.getElementById('delete-button');
    const fieldTanggal = document.getElementById('edit-tanggal');
    const fieldJenisPelanggaran = document.getElementById('edit-jenis-pelanggaran');
    const fieldKafarah = document.getElementById('edit-kafarah');
    const fieldJumlahSetor = document.getElementById('edit-jumlah-setor');
    const fieldTenggat = document.getElementById('edit-tenggat');

    // Kafarah mapping from backend
    const kafarahMapping = @json(\App\Models\Kafarah::KAFARAH_MAPPING);

    // Auto-fill kafarah when jenis pelanggaran changes
    fieldJenisPelanggaran?.addEventListener('change', function() {
      const selectedValue = this.value;
      if (selectedValue && kafarahMapping[selectedValue]) {
        fieldKafarah.value = kafarahMapping[selectedValue].kafarah;
      } else {
        fieldKafarah.value = '';
      }
    });

    const openEditModal = (data) => {
      editForm.action = `{{ url('/santri/kafarah') }}/${data.id}`;
      deleteForm.action = `{{ url('/santri/kafarah') }}/${data.id}`;
      fieldTanggal.value = data.tanggal;
      fieldJenisPelanggaran.value = data.jenisPelanggaran || '';
      fieldKafarah.value = data.kafarah || '';
      fieldJumlahSetor.value = data.jumlahSetor || '';
      fieldTenggat.value = data.tenggat || '';
      editModal.classList.remove('hidden');
      editModal.classList.add('flex');
    };

    const closeEdit = () => {
      editModal.classList.add('hidden');
      editModal.classList.remove('flex');
    };

    document.querySelectorAll('.action-menu-button').forEach(btn => {
      btn.addEventListener('click', () => {
        openEditModal({
          id: btn.dataset.id,
          tanggal: btn.dataset.tanggal,
          jenisPelanggaran: btn.dataset.jenisPelanggaran,
          kafarah: btn.dataset.kafarah,
          jumlahSetor: btn.dataset.jumlahSetor || '',
          tenggat: btn.dataset.tenggat || ''
        });
      });
    });

    [closeEditModal, cancelEdit].forEach(el => el?.addEventListener('click', closeEdit));
    editModal?.addEventListener('click', (e) => { if (e.target === editModal) closeEdit(); });

    deleteButton?.addEventListener('click', () => {
      if (confirm('Hapus kafarah ini?')) {
        deleteForm.submit();
      }
    });

    // Auto-hide toast banners
    document.querySelectorAll('.toast-banner[data-autohide]').forEach(toast => {
      const hide = () => {
        toast.classList.add('opacity-0', 'translate-y-2', 'transition', 'duration-500', 'ease-out');
        setTimeout(() => toast.remove(), 500);
      };
      setTimeout(hide, 2000);
    });
  });
</script>
@endif

@endsection
