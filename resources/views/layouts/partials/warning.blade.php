<div class="row">
    <div class="col-lg-8 offset-lg-2">
        @if(session()->has('warning'))
            <div class="alert alert-warning">
                <p class="font-weight-bold mb-1">{{ __('Warning!') }}</p>
                <p class="mb-0">{{ session()->get('warning') }}</p>
            </div>
        @endif
    </div>
</div>
