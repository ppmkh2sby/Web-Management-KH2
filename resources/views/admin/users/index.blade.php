<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">Users</h2>
    </x-slot>

    <div class="p-4 space-y-4 sm:p-6">
        {{-- Bar filter per role --}}
        <div class="flex flex-wrap gap-2">
            @php
                $allCount = array_sum($counts ?? []);
                $rolesMap = [
                    'admin'    => 'Admin',
                    'santri'   => 'Santri',
                    'wali'     => 'Wali',
                    'pengurus' => 'Pengurus',
                    'degur'    => 'Dewan Guru',
                ];
            @endphp
            <a href="{{ route('admin.users.index') }}"
               class="px-3 py-1 rounded border {{ !$role ? 'bg-slate-900 text-white' : 'bg-white' }}">
                Semua <span class="text-xs ml-1">({{ $allCount }})</span>
            </a>
            @foreach ($rolesMap as $val => $label)
                <a href="{{ route('admin.users.index', ['role' => $val, 'q' => $q]) }}"
                   class="px-3 py-1 rounded border {{ $role === $val ? 'bg-slate-900 text-white' : 'bg-white' }}">
                    {{ $label }}
                    <span class="text-xs ml-1">({{ $counts[$val] ?? 0 }})</span>
                </a>
            @endforeach
        </div>

        {{-- Pencarian --}}
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col gap-2 sm:flex-row">
            <input type="hidden" name="role" value="{{ $role }}">
            <x-text-input name="q" value="{{ $q }}" placeholder="Cari nama / email / no HP" class="w-full"/>
            <x-primary-button>Cari</x-primary-button>
            @if($q || $role)
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center justify-center px-3 py-2 border rounded">Reset</a>
            @endif
        </form>

        {{-- Tabel --}}
        <div class="bg-white shadow-sm rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="text-left px-4 py-2">Nama</th>
                        <th class="text-left px-4 py-2">Email</th>
                        <th class="text-left px-4 py-2">No. HP</th>
                        <th class="text-left px-4 py-2">Role</th>
                        <th class="text-left px-4 py-2">Info Khusus</th>
                        <th class="text-left px-4 py-2">Dibuat</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($users as $u)
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-2">
                                <div class="font-medium">{{ $u->name }}</div>
                            </td>
                            <td class="px-4 py-2">{{ $u->email }}</td>
                            <td class="px-4 py-2">{{ $u->phone }}</td>
                            <td class="px-4 py-2">
                                @php
                                    $label = $rolesMap[$u->role->value] ?? $u->role->value;
                                @endphp
                                <span class="px-2 py-0.5 rounded text-xs border">{{ $label }}</span>
                            </td>
                            <td class="px-4 py-2">
                                @if ($u->role->value === 'santri')
                                    Kode: <code>{{ optional($u->santri)->code }}</code>
                                @elseif ($u->role->value === 'wali')
                                    {{-- Bisa tampilkan jumlah anak terhubung bila diinginkan --}}
                                    {{-- {{ $u->waliOf()->count() }} anak --}}
                                    —
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-4 py-2 text-sm text-slate-600">{{ $u->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">Tidak ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        <div>{{ $users->links() }}</div>
    </div>
</x-app-layout>
