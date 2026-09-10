@extends('admin.layouts.app')

@section('title', 'Edit Content')
@section('page_heading', 'Edit Content')

@section('content')
<section class="ma-page-heading">
    <div><p class="ma-eyebrow">Content #{{ $page->id }}</p><h2>{{ $page->title }}</h2><p>Edit content and publishing state while preserving stable mobile links.</p></div>
    <a class="ma-button ma-button--outline" href="{{ route('admin.content.index') }}">Back to Content</a>
</section>

<div class="ma-detail-grid ma-content-edit-grid">
    <section class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Content</p><h3>Edit page</h3></div><span class="ma-badge {{ $page->is_published ? 'ma-badge--success' : 'ma-badge--purple' }}">{{ $page->is_published ? 'Published' : 'Draft' }}</span></div>
        <form class="ma-form" method="POST" action="{{ route('admin.content.update', $page) }}">
            @csrf @method('PUT')
            <div class="ma-form-grid">
                <div class="ma-field"><label for="title">Title</label><input id="title" name="title" value="{{ old('title', $page->title) }}" maxlength="180" required></div>
                <div class="ma-field"><label for="slug">Slug</label><input id="slug" name="slug" value="{{ old('slug', $page->slug) }}" maxlength="180" required @readonly($page->is_system)><small class="ma-field__hint">{{ $page->is_system ? 'Required legal-page slugs are locked.' : 'Changing a slug changes the mobile API URL.' }}</small></div>
            </div>
            <div class="ma-field"><label for="body">Content</label><textarea id="body" name="body" rows="18" maxlength="100000" required>{{ old('body', $page->body) }}</textarea></div>
            <div class="ma-field"><label for="sort_order">Sort order</label><input id="sort_order" name="sort_order" type="number" min="0" max="1000000" value="{{ old('sort_order', $page->sort_order) }}" required></div>
            <div class="ma-form-actions"><button class="ma-button ma-button--primary" type="submit">Save content</button></div>
        </form>
    </section>

    <aside class="ma-panel">
        <div class="ma-panel__header"><div><p class="ma-eyebrow">Publishing</p><h3>Page status</h3></div></div>
        <dl class="ma-definition-list">
            <div><dt>API slug</dt><dd>{{ $page->slug }}</dd></div>
            <div><dt>Page type</dt><dd>{{ $page->is_system ? 'Required system page' : 'Basic app content' }}</dd></div>
            <div><dt>Published</dt><dd>{{ $page->published_at?->format('M j, Y g:i A') ?? 'Not published' }}</dd></div>
            <div><dt>Updated</dt><dd>{{ $page->updated_at?->format('M j, Y g:i A') }}</dd></div>
        </dl>
        <div class="ma-stack-actions">
            <form method="POST" action="{{ route('admin.content.toggle-publish', $page) }}">@csrf @method('PATCH')<button class="ma-button {{ $page->is_published ? 'ma-button--outline' : 'ma-button--primary' }} ma-button--full" type="submit">{{ $page->is_published ? 'Move to draft' : 'Publish page' }}</button></form>
        </div>
        @unless($page->is_system)
            <div class="ma-callout ma-content-delete-callout"><strong>Delete optional content</strong><p>Deletion is soft. Required legal pages cannot be deleted.</p><form method="POST" action="{{ route('admin.content.destroy', $page) }}" onsubmit="return confirm('Delete this content page?')">@csrf @method('DELETE')<button class="ma-button ma-button--danger ma-button--full" type="submit">Delete page</button></form></div>
        @endunless
    </aside>
</div>
@endsection
