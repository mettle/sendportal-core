<div class="row">
    <div class="col-lg-8 offset-lg-2">
        @if(session()->has('success'))
            <div class="alert alert-success">
                <p class="font-weight-bold mb-1">{{ __('Success!') }}</p>
                <p class="mb-0">{{ session()->get('success') }}</p>
            </div>
        @endif
    </div>
</div>
