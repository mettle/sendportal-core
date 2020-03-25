@extends('layouts.app')

@section('title', __('Providers'))

@section('heading')
    {{ __('Providers') }}
@endsection

@section('content')

    @component('layouts.partials.actions')
        @slot('right')
            <a class="btn btn-primary btn-md btn-flat" href="{{ route('providers.create') }}">
                <i class="fa fa-plus mr-1"></i> {{ __('Add Provider') }}
            </a>
        @endslot
    @endcomponent

    <div class="card">
        <div class="card-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ __('Name') }}</th>
                        <th>{{ __('Provider') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($providers as $provider)
                        <tr>
                            <td>{{ $provider->name }}</td>
                            <td>{{ $provider->type->name }}</td>
                            <td>
                                <a class="btn btn-sm btn-light" href="{{ route('providers.edit', $provider->id) }}">{{ __('Edit') }}</a>
                                <form action="{{ route('providers.delete', $provider->id) }}" method="POST" style="display: inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-light">{{ __('Delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%">
                                <p class="empty-table-text">{{ __('You have not configured any providers.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
