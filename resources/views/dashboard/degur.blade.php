@extends('layouts.app')

@section('title', 'Dashboard Dewan Guru')
@section('content')
<div class="bg-white rounded-2xl shadow p-8">
    <h1 class="text-2xl font-bold">Dashboard Dewan Guru</h1>
    <p class="text-gray-600 mt-2">Selamat datang, {{ Auth::user()->name }}.</p>
</div>
@endsection
