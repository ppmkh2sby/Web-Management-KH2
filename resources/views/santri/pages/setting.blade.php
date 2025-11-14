@extends('layouts.santri-modern')
@section('title','Setting')

@section('content')
<div class="bg-white dark:bg-gray-900 rounded-2xl p-5 border dark:border-gray-800 shadow-sm">
  <h2 class="text-lg font-semibold mb-4">Pengaturan Akun</h2>
  <ul class="space-y-3 text-sm">
    <li class="flex items-center justify-between">
      <span>Mode Gelap</span>
      <button class="px-3 py-1.5 rounded-lg border dark:border-gray-700" x-data="{on:document.documentElement.classList.contains('dark')}" @click="on=!on; on?document.documentElement.classList.add('dark'):document.documentElement.classList.remove('dark'); localStorage.setItem('dark', on?'1':'0')">
        Toggle
      </button>
    </li>
    <li class="flex items-center justify-between">
      <span>Ganti Password</span>
      <a href="{{ route('password.request') }}" class="px-3 py-1.5 rounded-lg border hover:bg-gray-100 dark:border-gray-700 dark:hover:bg-gray-800">Atur</a>
    </li>
  </ul>
</div>
@endsection
