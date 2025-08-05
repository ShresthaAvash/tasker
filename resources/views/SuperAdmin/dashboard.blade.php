@extends('layouts.app')

@section('content_header')

    <div class="container mt-5">
        <h1 class="mb-4">Dashboard</h1>

        <a href="{{ route('superadmin.organizations.index') }}" class="btn btn-primary">
            View Organization
        </a>
    </div>

@endsection