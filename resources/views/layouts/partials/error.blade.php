<div class="row">
    <div class="col-lg-8 offset-lg-2">
        @if(session()->has('error'))
            <div class="alert alert-danger">
                <p class="font-weight-bold mb-1">{{ __('Error!') }}</p>
                <p class="mb-0">{{ session()->get('error') }}</p>
            </div>
        @endif
    </div>
</div>
