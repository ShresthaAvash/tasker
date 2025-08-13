<div class="mb-3">
    <label class="form-label">Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $subscription->name ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control">{{ old('description', $subscription->description ?? '') }}</textarea>
</div>
<div class="mb-3">
    <label class="form-label">Price</label>
    <input type="number" name="price" class="form-control" step="0.01" value="{{ old('price', $subscription->price ?? '') }}" required>
</div>
<div class="mb-3">
    <label class="form-label">Type</label>
    <select name="type" class="form-control" required>
        <option value="monthly" @if(old('type', $subscription->type ?? '') == 'monthly') selected @endif>Monthly</option>
        <option value="annually" @if(old('type', $subscription->type ?? '') == 'annually') selected @endif>Annually</option>
    </select>
</div>