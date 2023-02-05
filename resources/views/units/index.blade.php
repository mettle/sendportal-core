@extends('sendportal::layouts.app')

@section('title', __('Units'))

@section('heading', __('Manage Units'))

@section('content')

    <!-- @include('sendportal::messages.partials.nav') -->

    @component('sendportal::layouts.partials.actions')
        @slot('left')
           
            
           
        @endslot
    @endcomponent
    <div class="row my-4">
        <div class="col-md-8 col-12">
            <form action="{{ route('sendportal.messages.index') }}" method="GET" class="form-inline">
                <div class="mr-2">
                    <input type="text" class="form-control" placeholder="Search..." name="search"
                        value="{{ request('search') }}">
                </div>

            
                <button type="submit" class="btn btn-light">{{ __('Search') }}</button>

                @if(request()->anyFilled(['search', 'status']))
                    <a href="{{ route('sendportal.messages.index') }}" class="btn btn-light">{{ __('Clear') }}</a>
                @endif
            </form>
        </div>
        <div class="col-md-4 col-12 text-right">
            <a class="btn btn-primary btn-md btn-flat" href="{{ route('sendportal.units.load') }}">
                <i class="fa fa-plus mr-1"></i> {{ __('Load Units') }}
            </a>
        </div>
    </div>
    <div class="card">
        <div class="card-table table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>{{ __('Fullname') }}</th>
                    <th>{{ __('Old Balance') }}</th>
                    <th>{{ __('Action') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('New Balance') }}</th>
                </tr>
                </thead>
                <tbody>
                    @forelse($unit_history as $history)
                        <tr>
                            <td>
                                {{ $history->user_unit->user->name }}
                            </td>
                            <td>{{ $history->old_unit_balance }}</td>
                            <td>
                                {{ $history->action }}
                            </td>
                            <td>
                                {{ $history->amount }}
                            </td>
                            <td>
                                {{ $history->new_unit_balance }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%">
                                <p class="empty-table-text">{{ __('There are no Histories Found') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @include('sendportal::layouts.partials.pagination', ['records' => $unit_history])

    @push('js')
        <script>
            $(function () {
                $('#select-all').click(function () {
                    $('.message-select').prop('checked', true);
                });

                $('.delete-message').submit(function (event) {
                    event.preventDefault();

                    let confirmDelete = confirm('Are you sure you want to delete this message?');

                    if(confirmDelete) {
                        this.submit();
                    }
                });
            })
        </script>
    @endpush

@endsection
