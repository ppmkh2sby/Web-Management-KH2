@if($isStaffViewer)
  <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    @if($logRows->isEmpty())
      <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
        Belum ada data log keluar/masuk.
      </div>
    @else
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-100 text-left text-gray-500">
              <th class="py-2 pr-4">Tanggal</th>
              <th class="py-2 pr-4">Santri</th>
              <th class="py-2 pr-4">Gender</th>
              <th class="py-2 pr-4">Tujuan</th>
              <th class="py-2 pr-4">Keluar</th>
              <th class="py-2 pr-4">Masuk</th>
              <th class="py-2">Catatan</th>
            </tr>
          </thead>
          <tbody>
            @foreach($logRows as $log)
              @php
                $gender = strtolower((string) ($log->santri->gender ?? ''));
                $genderLabel = $gender === 'putra' ? 'Putra' : ($gender === 'putri' ? 'Putri' : '-');
                $genderClass = $gender === 'putra'
                  ? 'bg-blue-100 text-blue-700'
                  : ($gender === 'putri' ? 'bg-rose-100 text-rose-700' : 'bg-gray-100 text-gray-600');
              @endphp
              <tr class="border-b border-gray-50">
                <td class="py-2 pr-4">{{ optional($log->tanggal_pengajuan)->translatedFormat('d M Y') }}</td>
                <td class="py-2 pr-4 font-medium text-gray-900">{{ $log->santri->nama_lengkap ?? '-' }}</td>
                <td class="py-2 pr-4">
                  <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $genderClass }}">{{ $genderLabel }}</span>
                </td>
                <td class="py-2 pr-4">{{ $log->jenis }}</td>
                <td class="py-2 pr-4">{{ $log->waktu_keluar ?: '-' }}</td>
                <td class="py-2 pr-4">{{ $log->waktu_masuk ?: '-' }}</td>
                <td class="py-2">{{ $log->catatan ?: '-' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif

    @if($isPaginated && method_exists($logs, 'hasPages') && $logs->hasPages())
      <div class="mt-4 border-t border-gray-100 pt-3" data-log-pagination>
        {{ $logs->links() }}
      </div>
    @endif
  </div>
@else
  @if($logRows->isEmpty())
    <div class="rounded-2xl border border-dashed border-gray-200 bg-white p-6 text-center text-sm text-gray-500">
      Belum ada data log keluar/masuk.
    </div>
  @else
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-gray-100 text-left text-gray-500">
              <th class="py-2 pr-4">Tanggal</th>
              <th class="py-2 pr-4">Tujuan</th>
              <th class="py-2 pr-4">Waktu Keluar</th>
              <th class="py-2 pr-4">Waktu Masuk</th>
              <th class="py-2">Catatan</th>
            </tr>
          </thead>
          <tbody>
            @foreach($logRows as $log)
              <tr class="border-b border-gray-50">
                <td class="py-2 pr-4">{{ optional($log->tanggal_pengajuan)->translatedFormat('d M Y') }}</td>
                <td class="py-2 pr-4 font-medium text-gray-900">{{ $log->jenis }}</td>
                <td class="py-2 pr-4">{{ $log->waktu_keluar ?: '-' }}</td>
                <td class="py-2 pr-4">{{ $log->waktu_masuk ?: '-' }}</td>
                <td class="py-2">{{ $log->catatan ?: '-' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif

  @if($isPaginated && method_exists($logs, 'hasPages') && $logs->hasPages())
    <div class="rounded-2xl border border-gray-100 bg-white p-4 shadow-sm" data-log-pagination>
      {{ $logs->links() }}
    </div>
  @endif
@endif
