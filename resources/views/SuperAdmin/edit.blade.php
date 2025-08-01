@extends('layouts.app')

@section('page-content')

    <div class="card">
        <div class="card-header">
            <h4>Add New User</h4>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ route('superadmin.organizations.store') }}">
                @csrf
            <input type="hidden" name="id" value="{{$user->id}}">

                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" value ="{{$user->name}}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value ="{{$user->email}}"required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control"  value ="{{$user->phone}}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Address</label>
                    <textarea name="address" class="form-control">{{$user->address}}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>


                <button type="submit" class="btn btn-primary">Edit User</button>
            </form>
        </div>
    </div>
@endsection

