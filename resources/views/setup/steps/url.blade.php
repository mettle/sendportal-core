<div class="card-header {{ $step['completed'] ? 'bg-success text-white' : ($active ? 'bg-light' : null) }}">
    <h6 class="mb-0">
        {{ $step['completed'] ? '✔' : ($active ? '➡️' : null) }} {{ $step['name'] }}
        @if($step['completed'])
            - set to <code>{{ config('app.url') }}</code>
        @endif
        <span class="text-small float-right"><em>{{ $iteration }}/{{ $total }}</em></span>
    </h6>
</div>
<div class="collapse {{ $active ? 'show' : null }}">
    <div class="card-body">
        @if ($step['completed'])
            <p>✔️ The Application url is set to <code>{{ config('app.url') }}</code></p>
            <button class="btn btn-primary btn-sm" wire:click="next">Next</button>
        @else
            <form wire:submit.prevent="run(Object.fromEntries(new FormData($event.target)))">
                <div class="form-group">
                    <label for="url">Application Url</label>
                    <input type="url" class="form-control" id="url" name="url" placeholder="https://sendportal.yourdomain.com" required>
                    @error('url') <span class="form-text text-danger">{{ $message }}</span>@enderror
                </div>
                <button class="btn btn-primary btn-sm" type="submit">Submit</button>
            </form>
        @endif
    </div>
</div>