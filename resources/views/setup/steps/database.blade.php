<div class="card-header {{ $step['completed'] ? 'bg-success text-white' : ($active ? 'bg-light' : null) }}">
    <h6 class="mb-0">
        {{ $step['completed'] ? '✔' : ($active ? '➡️' : null) }} {{ $step['name'] }}
        <span class="text-small float-right"><em>{{ $iteration }}/{{ $total }}</em></span>
    </h6>
</div>
<div class="collapse {{ $active ? 'show' : null }}">
    <div class="card-body">
        @if ($step['completed'])
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
    </div>
</div>