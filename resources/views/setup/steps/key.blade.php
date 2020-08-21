@if ($completed)
    <p>✔️ The Application key has been set</p>
    <button class="btn btn-primary btn-sm" wire:click="next">Next</button>
@else
    <p>The Application key has not been set. Would you like to set it now?</p>
    <button class="btn btn-primary btn-sm" wire:click="run">Run</button>
@endif