@extends('admin.layouts.app')

@section('title', 'Hearts & Saved Makers')
@section('page_heading', 'Hearts & Saved Makers')

@section('content')
<section class="ma-page-heading">
    <div>
        <p class="ma-eyebrow">Relationship Management</p>
        <h2>Hearts & Saved Makers</h2>
        <p>Inspect which Appreciators have saved which Makers, review platform save totals, and identify the Makers receiving the most interest.</p>
    </div>
    <span class="ma-pill">{{ number_format($summary['total_saves']) }} total saves</span>
</section>

<section class="ma-stat-grid ma-save-stat-grid" aria-label="Save statistics">
    @include('admin.partials.stat-card', ['label' => 'Total Hearts / Saves', 'value' => $summary['total_saves'], 'hint' => 'Current Appreciator-to-Maker relationships'])
    @include('admin.partials.stat-card', ['label' => 'Saving Appreciators', 'value' => $summary['saving_appreciators'], 'hint' => 'Accounts with at least one save'])
    @include('admin.partials.stat-card', ['label' => 'Saved Makers', 'value' => $summary['saved_makers'], 'hint' => 'Makers saved at least once'])
</section>

<section class="ma-panel ma-top-makers-panel">
    <div class="ma-panel__header">
        <div><p class="ma-eyebrow">Leaderboard</p><h3>Most Hearted Makers</h3></div>
        <span class="ma-panel__meta">Top 5</span>
    </div>

    @if ($topMakers->isEmpty())
        <div class="ma-empty-state ma-empty-state--compact"><span class="ma-empty-state__icon" aria-hidden="true">◇</span><h4>No Maker saves yet</h4><p>The leaderboard will populate as Appreciators use the Heart action.</p></div>
    @else
        <ol class="ma-ranked-list">
            @foreach ($topMakers as $maker)
                <li>
                    <span class="ma-ranked-list__rank">{{ $loop->iteration }}</span>
                    <div class="ma-ranked-list__content">
                        <strong><a class="ma-text-link" href="{{ route('admin.makers.show', $maker) }}">{{ $maker->name }}</a></strong>
                        <small>{{ ucfirst($maker->status->value) }} Maker</small>
                    </div>
                    <a class="ma-ranked-list__count" href="{{ route('admin.saves.index', ['maker_id' => $maker->id]) }}" aria-label="View saves for {{ $maker->name }}">{{ number_format($maker->saves_count) }} saves</a>
                </li>
            @endforeach
        </ol>
    @endif
</section>

@if ($filteredMaker)
    <div class="ma-alert ma-alert--info ma-save-filter-notice" role="status">
        <div><strong>Maker filter active</strong><p>Showing relationships for {{ $filteredMaker->name }}.</p></div>
        <a class="ma-button ma-button--outline ma-button--compact" href="{{ route('admin.saves.index') }}">Clear Maker filter</a>
    </div>
@endif

<section class="ma-panel ma-panel--filters">
    <form class="ma-save-filter-grid" method="GET" action="{{ route('admin.saves.index') }}">
        @if (! empty($filters['maker_id']))<input type="hidden" name="maker_id" value="{{ $filters['maker_id'] }}">@endif
        @if (! empty($filters['appreciator_id']))<input type="hidden" name="appreciator_id" value="{{ $filters['appreciator_id'] }}">@endif
        <div class="ma-field">
            <label for="search">Search relationships</label>
            <input id="search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" placeholder="Maker or Appreciator name/email">
        </div>
        <div class="ma-filter-actions">
            <button class="ma-button ma-button--primary" type="submit">Apply search</button>
            <a class="ma-button ma-button--outline" href="{{ route('admin.saves.index') }}">Reset</a>
        </div>
    </form>
</section>

<section class="ma-panel">
    <div class="ma-panel__header"><div><p class="ma-eyebrow">Relationship Directory</p><h3>Maker save details</h3></div><span class="ma-panel__meta">20 per page</span></div>

    @if ($saves->isEmpty())
        <div class="ma-empty-state"><span class="ma-empty-state__icon" aria-hidden="true">◇</span><h4>No matching save relationships</h4><p>Try clearing the current search or Maker filter.</p></div>
    @else
        <div class="ma-table-wrap">
            <table class="ma-table ma-save-table">
                <thead><tr><th scope="col">Appreciator</th><th scope="col">Maker</th><th scope="col">Saved at</th><th scope="col">Details</th></tr></thead>
                <tbody>
                    @foreach ($saves as $save)
                        <tr>
                            <td data-label="Appreciator"><strong>{{ $save->appreciator->name }}</strong><small>{{ $save->appreciator->email }}</small></td>
                            <td data-label="Maker"><strong>{{ $save->maker->name }}</strong><small>{{ $save->maker->email }}</small></td>
                            <td data-label="Saved at">{{ $save->created_at?->format('M j, Y g:i A') }}</td>
                            <td data-label="Details" class="ma-table-actions"><a class="ma-text-link" href="{{ route('admin.users.show', $save->appreciator) }}">Appreciator</a><a class="ma-text-link" href="{{ route('admin.makers.show', $save->maker) }}">Maker</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @include('admin.partials.pagination', ['paginator' => $saves])
    @endif
</section>
@endsection
