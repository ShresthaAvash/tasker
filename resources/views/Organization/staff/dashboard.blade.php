@extends('layouts.app')

@section('page-content')

    <div class="container">
        <h3>Welcome, {{ auth()->user()->name }}</h3>
        <p>This is the Staff Dashboard.</p>
    </div>

@endsection

