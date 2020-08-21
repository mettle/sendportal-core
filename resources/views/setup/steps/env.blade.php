@if ($completed)
    <p>✔️ <code>.env</code> file already exists</p>
    <button class="btn btn-primary btn-sm" wire:click="next">Next</button>
@else
    <p>The .env file does not yet exist. Would you like to create it now?</p>
    <button class="btn btn-primary btn-sm" wire:click="run" wire:loading.attribute="disabled">
        <span wire:loading class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
        Run
    </button>
@endif