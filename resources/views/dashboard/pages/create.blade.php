@extends('layouts.dashboard')

@section('title', 'New Page')

@section('content')
<form method="POST" action="{{ route('dashboard.pages.store') }}">
    @csrf
    @include('dashboard.pages.form', ['page' => new \App\Models\Post])
</form>
@endsection
