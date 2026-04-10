@extends('layouts.dashboard')

@section('title', 'Edit Page')

@section('content')
<form method="POST" action="{{ route('dashboard.pages.update', $page) }}">
    @csrf
    @method('PUT')
    @include('dashboard.pages.form', ['page' => $page])
</form>
@endsection
