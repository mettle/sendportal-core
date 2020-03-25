@extends('sendportal::layouts.app')

@section('title', __('Segments'))

@section('heading')
    {{ __('Segments') }}
@endsection

@section('content')
    @component('layouts.partials.actions')

        @slot('right')
            <a class="btn btn-primary btn-md btn-flat" href="{{ route('segments.create') }}">
                <i class="fa fa-plus"></i> {{ __('New Segment') }}
            </a>
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-table">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Subscribers') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
                </thead>
                <tbody>
                    @forelse($segments as $segment)
                        <tr>
                            <td>
                                <a href="{{ route('segments.edit', $segment->id) }}">
                                    {{ $segment->name }}
                                </a>
                            </td>
                            <td>{{ $segment->subscribers_count }}</td>
                            <td><a class="btn btn-sm btn-light" href="{{ route('segments.edit', $segment->id) }}">{{ __('Edit') }}</a></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%">
                                <p class="empty-table-text">{{ __('You have not created any segments.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
