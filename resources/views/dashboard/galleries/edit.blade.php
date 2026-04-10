@extends('layouts.dashboard')

@section('title', 'Edit Gallery')

@section('content')
<form method="POST" action="{{ route('dashboard.galleries.update', $gallery) }}">
    @csrf
    @method('PUT')
    @include('dashboard.galleries.form', ['gallery' => $gallery])
</form>
@endsection
