@extends('layouts.dashboard')

@section('title', 'Edit Post')

@section('content')
<form method="POST" action="{{ route('dashboard.posts.update', $post) }}">
    @csrf
    @method('PUT')
    @include('dashboard.posts.form', ['post' => $post])
</form>
@endsection
