@extends('layouts.dashboard')

@section('title', 'Edit Tag')

@section('content')
<form method="POST" action="{{ route('dashboard.tags.update', $tag) }}">
    @csrf
    @method('PUT')
    @include('dashboard.tags.form', ['tag' => $tag])
</form>
@endsection
