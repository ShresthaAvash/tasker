@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Dashboard') }}</div>
                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ((auth()->user()->type)=='S')
                    <p><strong>{{ auth()->user()->name}}! </strong>{{ __('You are logged in as ') }} SuperAdmin</p> 
                    @endif

                    @if ((auth()->user()->type)=='O')
                    <p><strong>{{ auth()->user()->name}}! </strong>{{ __('You are logged in as') }}Organization</p> 
                    @endif

                    @if ((auth()->user()->type)=='C')
                    <p><strong>{{ auth()->user()->name}}! </strong>{{ __('You are logged in as') }}Client.</p> 
                    @endif

                    @if ((auth()->user()->type)=='T')
                    <p><strong>{{ auth()->user()->name}}!</strong>{{ __('You are logged in as ') }} Staff.</p> 
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
