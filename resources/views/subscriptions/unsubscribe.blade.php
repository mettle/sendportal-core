@extends('sendportal::layouts.subscriptions')

@section('content')
    <div class="text-center">
        <h1>{{ __('Unsubscribe') }}</h1>
        <p>{!! __('Remove <b>:email</b> from this email list?', ['email' => $message->subscriber->email])  !!}</p>

        <form action="{{ route('sendportal.subscriptions.update', $message->hash) }}" method="post">
            @csrf
            <input type="hidden" name="_method" value="put">
            <input type="hidden" name="unsubscribed" value="1">
            <button type="submit" class="btn btn-sm btn-primary">{{ __('Unsubscribe now') }}</button>
        </form>
    </div>
@endsection
