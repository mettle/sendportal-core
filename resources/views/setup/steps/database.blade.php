@if ($completed)
    <p>✔️ Database connection successful.</p>
    <button class="btn btn-primary btn-sm" wire:click="next">Next</button>
@else
    <p>✖️ A database connection could not be established. Please update your configuration and try again.</p>
    @if(config('database.default'))
        <p>Default Connection: <code>{{ config('database.default') }}</code></p>

        <button class="btn btn-primary btn-sm" wire:click="checkAgain" wire:loading.attr="disabled">
            <span wire:loading class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Try Again
        </button>
    @endif
@endif
