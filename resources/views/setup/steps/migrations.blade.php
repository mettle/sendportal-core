@if ($completed)
    <p>✔️ Database migrations are up to date</p>
    <button class="btn btn-primary btn-sm" wire:click="next">Next</button>
@else
    <p>There are pending database migrations. Would you like to run migrations now?</p>
    <button class="btn btn-primary btn-sm" wire:click="run" wire:loading.attr="disabled">
        <span wire:loading class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        Run
    </button>
@endif
