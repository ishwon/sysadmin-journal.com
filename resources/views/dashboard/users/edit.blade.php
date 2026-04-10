@extends('layouts.dashboard')

@section('title', 'Edit User')

@section('content')
<form method="POST" action="{{ route('dashboard.users.update', $user) }}">
    @csrf
    @method('PUT')
    @include('dashboard.users.form', ['user' => $user])
</form>
@endsection
