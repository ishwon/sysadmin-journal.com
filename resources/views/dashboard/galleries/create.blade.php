@extends('layouts.dashboard')

@section('title', 'New Gallery')

@section('content')
<form method="POST" action="{{ route('dashboard.galleries.store') }}">
    @csrf
    @include('dashboard.galleries.form', ['gallery' => new \App\Models\Gallery])
</form>
@endsection
