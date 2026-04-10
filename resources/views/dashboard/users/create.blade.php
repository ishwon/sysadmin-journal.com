@extends('layouts.dashboard')

@section('title', 'New User')

@section('content')
<form method="POST" action="{{ route('dashboard.users.store') }}">
    @csrf
    @include('dashboard.users.form', ['user' => new \App\Models\User])
</form>
@endsection
