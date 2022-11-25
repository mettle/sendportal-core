@extends('sendportal::layouts.app')

@section('title', __('Message'))

@section('heading', __('Draft Message'))

@section('content')

    <script>
        function resizeTextArea(element) {
            newHeight = element.contentWindow.document.body.scrollHeight;
            element.height = (newHeight + 10) + "px";
        }

    </script>

    <div class="card mb-5">
        <div class="card-header card-header-accent">
            <div class="card-header-inner">
                <div class="float-right">
                    @if ($message->sent_at)
                        {{ __('Sent') }} <span
                            title="{{ $message->sent_at }}">{{ $message->sent_at->diffForHumans() }}</span>
                    @else
                        <form action="{{ route('sendportal.messages.send') }}" method="post">
                            @csrf
                            <input type="hidden" name="id" value="{{ $message->id }}">
                            <button type="submit" class="btn btn-sm btn-primary">{{ __('Send now') }}</button>
                        </form>
                    @endif
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
        <div class="card-body">
            <table>
                <tbody>
                <tr>
                    <td width="75px"><b>To:</b></td>
                    <td>{{ $message->recipient_email }}</td>
                </tr>
                <tr>
                    <td><b>{{ __('Subject') }}:</b></td>
                    <td>{{ $subject }}</td>
                </tr>
                <tr>
                    <td><b>{{ __('From') }}:</b></td>
                    <td>{{ $message->from_name }} &lt;{{ $message->from_email }}&gt;</td>
                </tr>
                </tbody>
            </table>

            <hr>

            <iframe width="100%" height="100%" scrolling="no" frameborder="0" srcdoc="{{ $content }}" onload="resizeTextArea(this)"></iframe>
        </div>
    </div>

@endsection


