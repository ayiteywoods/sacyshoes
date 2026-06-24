@foreach ($categoryTree as $parentCategory)
    <option value="{{ $parentCategory->id }}" @selected((string) ($selected ?? '') === (string) $parentCategory->id)>{{ $parentCategory->name }}</option>
    @foreach ($parentCategory->children as $childCategory)
        <option value="{{ $childCategory->id }}" @selected((string) ($selected ?? '') === (string) $childCategory->id)>&nbsp;&nbsp;↳ {{ $childCategory->name }}</option>
    @endforeach
@endforeach
