@extends('layouts.dashboard')

@section('title', 'New Post')

@section('content')
<form method="POST" action="{{ route('dashboard.posts.store') }}">
    @csrf
    @include('dashboard.posts.form', ['post' => new \App\Models\Post])
</form>
@endsection
