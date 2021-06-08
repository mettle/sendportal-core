@extends('sendportal::layouts.subscriptions')

@section('content')
    <div class="text-center">
        <h1>{{ __('Resubscribe') }}</h1>
        <p>{!! __('Add <b>:email</b> to this email list?', ['email' => $message->subscriber->email])  !!}</p>

        <form action="{{ route('sendportal.subscriptions.update', $message->hash) }}" method="post">
            @csrf
            <input type="hidden" name="_method" value="put">
            <input type="hidden" name="unsubscribed" value="0">
            <button type="submit" class="btn btn-sm btn-primary">{{ __('Resubscribe now') }}</button>
        </form>
    </div>
@endsection
