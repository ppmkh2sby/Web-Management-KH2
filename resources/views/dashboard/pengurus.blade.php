@extends('layouts.app')

@section('title', 'Dashboard Pengurus')
@section('content')
<div class="bg-white rounded-2xl shadow p-8">
    <h1 class="text-2xl font-bold">Dashboard Pengurus</h1>
    <p class="text-gray-600 mt-2">Selamat datang, {{ Auth::user()->name }}.</p>
</div>
@endsection
