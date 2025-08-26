{{-- tasker/resources/views/Organization/Clients/partials/general.blade.php --}}
<form action="{{ route('clients.update', $client->id) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="form-group"><label>Client Name</label><input type="text" class="form-control" name="name" value="{{ old('name', $client->name) }}" required></div>
    <div class="form-group"><label>Client Email</label><input type="email" class="form-control" name="email" value="{{ old('email', $client->email) }}" required></div>
    <div class="form-group"><label>Client Phone</label><input type="text" class="form-control" name="phone" value="{{ old('phone', $client->phone) }}"></div>
    <div class="form-group"><label>Client Address</label><textarea class="form-control" name="address">{{ old('address', $client->address) }}</textarea></div>
    <div class="form-group"><label>Client Photo</label>@if($client->photo)<div><img src="{{ asset('storage/'.$client->photo) }}" alt="Photo" width="100" class="mb-2 img-thumbnail"></div>@endif<input type="file" class="form-control-file" name="photo" accept="image/*"></div>
    <div class="form-group"><label>Status</label><select class="form-control" name="status" required><option value="A" @if(old('status', $client->status) == 'A') selected @endif>Active</option><option value="I" @if(old('status', $client->status) == 'I') selected @endif>Inactive</option></select></div>
    <hr><p class="text-muted">Password (leave blank to keep current)</p>
    <div class="row"><div class="col-md-6"><div class="form-group"><label>New Password</label><input type="password" class="form-control" name="password"></div></div><div class="col-md-6"><div class="form-group"><label>Confirm Password</label><input type="password" class="form-control" name="password_confirmation"></div></div></div>
    <button type="submit" class="btn btn-primary">Save Changes</button> <a href="{{ route('clients.index') }}" class="btn btn-secondary">Back to List</a>
</form>