@extends('admin.layouts.app')

@section('title', 'Content')
@section('page_heading', 'Content')

@section('content')
<section class="ma-page-heading">
    <div>
        <p class="ma-eyebrow">App Content & Legal</p>
        <h2>Content Library</h2>
        <p>Manage Terms & Conditions, Privacy Policy, and additional basic app content without editing code.</p>
    </div>
    <span class="ma-pill">{{ number_format($summary['published']) }} published</span>
</section>

<section class="ma-stat-grid ma-content-stat-grid" aria-label="Content statistics">
    @include('admin.partials.stat-card', ['label' => 'Total Pages', 'value' => $summary['total'], 'hint' => 'Legal and app content'])
    @include('admin.partials.stat-card', ['label' => 'Published', 'value' => $summary['published'], 'hint' => 'Visible through the mobile API'])
    @include('admin.partials.stat-card', ['label' => 'Drafts', 'value' => $summary['drafts'], 'hint' => 'Not visible to app users'])
    @include('admin.partials.stat-card', ['label' => 'Required Pages', 'value' => $summary['system'], 'hint' => 'Protected legal content'])
</section>

<div class="ma-content-grid">
    <section class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">New Content</p><h3>Create app page</h3></div></div>
        <form class="ma-form" method="POST" action="{{ route('admin.content.store') }}">
            @csrf
            <div class="ma-form-grid ma-content-title-slug-grid">
                <div class="ma-field"><label for="title">Title</label><input id="title" name="title" value="{{ old('title') }}" maxlength="180" required></div>
                <div class="ma-field"><label for="slug">Slug</label><input id="slug" name="slug" value="{{ old('slug') }}" maxlength="180" placeholder="about-ma-motion" required><small class="ma-field__hint">Stable mobile/API identifier.</small></div>
            </div>
            <div class="ma-field"><label for="body">Content</label><textarea id="body" name="body" rows="9" maxlength="100000" required>{{ old('body') }}</textarea><small class="ma-field__hint">Plain text content. The mobile client controls final typography.</small></div>
            <div class="ma-form-grid">
                <div class="ma-field"><label for="sort_order">Sort order</label><input id="sort_order" name="sort_order" type="number" min="0" max="1000000" value="{{ old('sort_order', 100) }}" required></div>
                <div class="ma-field ma-checkbox-field"><label><input type="checkbox" name="is_published" value="1" @checked(old('is_published'))><span>Publish immediately</span></label></div>
            </div>
            <div class="ma-form-actions"><button class="ma-button ma-button--primary" type="submit">Create page</button></div>
        </form>
    </section>

    <aside class="ma-panel ma-content-guidance">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Publishing Rules</p><h3>Safe content management</h3></div></div>
        <div class="ma-callout"><strong>Terms & Conditions + Privacy Policy</strong><p>These required pages are protected from deletion and keep stable slugs so mobile links do not break.</p></div>
        <div class="ma-callout"><strong>Draft first</strong><p>Unpublished content is never returned by the public app-content API. Publish only after the text is ready.</p></div>
    </aside>
</div>

<section class="ma-panel ma-panel--filters">
    <form class="ma-content-filter-grid" method="GET" action="{{ route('admin.content.index') }}">
        <div class="ma-field"><label for="search">Search content</label><input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Title, slug or content"></div>
        <div class="ma-field"><label for="status">Status</label><select id="status" name="status"><option value="">All statuses</option><option value="published" @selected(($filters['status'] ?? '') === 'published')>Published</option><option value="draft" @selected(($filters['status'] ?? '') === 'draft')>Draft</option></select></div>
        <div class="ma-filter-actions"><button class="ma-button ma-button--primary" type="submit">Apply filters</button><a class="ma-button ma-button--outline" href="{{ route('admin.content.index') }}">Reset</a></div>
    </form>
</section>

<section class="ma-panel">
    <div class="ma-panel__header"><div><p class="ma-eyebrow">Content Directory</p><h3>Managed app pages</h3></div><span class="ma-panel__meta">20 per page</span></div>
    @if($pages->isEmpty())
        <div class="ma-empty-state"><span class="ma-empty-state__icon" aria-hidden="true">◇</span><h4>No matching content</h4><p>Create a page or change the filters.</p></div>
    @else
        <div class="ma-table-wrap">
            <table class="ma-table ma-content-table">
                <thead><tr><th scope="col">Page</th><th scope="col">Status</th><th scope="col">Type</th><th scope="col">Updated</th><th scope="col">Actions</th></tr></thead>
                <tbody>
                @foreach($pages as $page)
                    <tr>
                        <td data-label="Page"><strong>{{ $page->title }}</strong><small>/{{ $page->slug }}</small></td>
                        <td data-label="Status"><span class="ma-badge {{ $page->is_published ? 'ma-badge--success' : 'ma-badge--purple' }}">{{ $page->is_published ? 'Published' : 'Draft' }}</span></td>
                        <td data-label="Type">{{ $page->is_system ? 'Required' : 'App content' }}</td>
                        <td data-label="Updated">{{ $page->updated_at?->format('M j, Y g:i A') }}</td>
                        <td data-label="Actions" class="ma-table-actions"><a class="ma-text-link" href="{{ route('admin.content.edit', $page) }}">Edit</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator' => $pages])
    @endif
</section>
@endsection
