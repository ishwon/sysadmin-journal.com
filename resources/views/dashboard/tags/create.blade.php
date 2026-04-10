@extends('layouts.dashboard')

@section('title', 'New Tag')

@section('content')
<form method="POST" action="{{ route('dashboard.tags.store') }}">
    @csrf
    @include('dashboard.tags.form', ['tag' => new \App\Models\Tag])
</form>
@endsection
