@extends('admin.layouts.app')

@section('title', $pageTitle)
@section('page_heading', $pageTitle)

@section('content')
<section class="ma-page-heading">
    <div><p class="ma-eyebrow">Discovery Taxonomy</p><h2>{{ $pageTitle }}</h2><p>{{ $description }}</p></div>
    <span class="ma-pill">{{ number_format($items->total()) }} matching {{ strtolower($pageTitle) }}</span>
</section>

@if ($errors->any())
    <div class="ma-alert ma-alert--error" role="alert"><div><strong>Please correct the following.</strong><ul class="ma-error-list">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>
@endif

<div class="ma-detail-grid">
    <section class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Add {{ $singular }}</p><h3>New {{ strtolower($singular) }}</h3></div></div>
        <form class="ma-form" method="POST" action="{{ route($routePrefix.'.store') }}">
            @csrf
            <div class="ma-field"><label for="name">Name</label><input id="name" name="name" type="text" maxlength="120" value="{{ old('name') }}" required></div>
            <div class="ma-field"><label for="sort_order">Sort order</label><input id="sort_order" name="sort_order" type="number" min="0" max="1000000" value="{{ old('sort_order', 0) }}" required><small class="ma-field__hint">Lower numbers appear first.</small></div>
            <input type="hidden" name="is_active" value="0">
            <label class="ma-checkbox"><input name="is_active" type="checkbox" value="1" @checked((string) old('is_active', '1') === '1')><span>Enabled for future app workflows and filters</span></label>
            <div class="ma-form-actions"><button class="ma-button ma-button--primary" type="submit">Add {{ $singular }}</button></div>
        </form>
    </section>

    <section class="ma-panel ma-panel--filters">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Find</p><h3>Filter {{ strtolower($pageTitle) }}</h3></div></div>
        <form class="ma-form" method="GET" action="{{ route($routePrefix.'.index') }}">
            <div class="ma-field"><label for="search">Search</label><input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search by name"></div>
            <div class="ma-field"><label for="status">Status</label><select id="status" name="status"><option value="">All statuses</option><option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option><option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option></select></div>
            <div class="ma-form-actions"><button class="ma-button ma-button--primary" type="submit">Apply filters</button><a class="ma-button ma-button--outline" href="{{ route($routePrefix.'.index') }}">Reset</a></div>
        </form>
    </section>
</div>

<section class="ma-panel">
    <div class="ma-panel__header"><div><p class="ma-eyebrow">Managed Options</p><h3>{{ $pageTitle }}</h3></div><span class="ma-panel__meta">20 per page</span></div>
    @if ($items->isEmpty())
        <div class="ma-empty-state"><span class="ma-empty-state__icon" aria-hidden="true">◇</span><h4>No matching {{ strtolower($pageTitle) }}</h4><p>Add one or clear the filters.</p></div>
    @else
        <div class="ma-table-wrap"><table class="ma-table"><thead><tr><th>Name</th><th>Slug</th><th>Order</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        @foreach ($items as $item)
            <tr><td data-label="Name"><strong>{{ $item->name }}</strong></td><td data-label="Slug"><small>{{ $item->slug }}</small></td><td data-label="Order">{{ $item->sort_order }}</td><td data-label="Status"><span class="ma-badge {{ $item->is_active ? 'ma-badge--success' : 'ma-badge--muted' }}">{{ $item->is_active ? 'Active' : 'Inactive' }}</span></td><td data-label="Actions"><div class="ma-form-actions"><a class="ma-button ma-button--outline" href="{{ route($routePrefix.'.edit', $item) }}">Edit</a><form method="POST" action="{{ route($routePrefix.'.toggle-status', $item) }}">@csrf @method('PATCH')<button class="ma-button ma-button--outline" type="submit">{{ $item->is_active ? 'Disable' : 'Enable' }}</button></form><form method="POST" action="{{ route($routePrefix.'.destroy', $item) }}" onsubmit="return confirm('Delete this {{ strtolower($singular) }}? Existing historical references remain protected by soft deletion.');">@csrf @method('DELETE')<button class="ma-button ma-button--danger" type="submit">Delete</button></form></div></td></tr>
        @endforeach
        </tbody></table></div>
        @include('admin.partials.pagination', ['paginator' => $items])
    @endif
</section>
@endsection
