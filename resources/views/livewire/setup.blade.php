<div>
    <h2 class="text-center">Application Setup</h2>
    <div class="text-center m-2 invisible" wire:loading.class.remove="invisible">
        <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>

    @if (session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="progress mb-2" style="height: 4px">
        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: {{ $this->progress }}%"></div>
    </div>
    <div class="accordion">
        @foreach ($steps as $index => $step)
            <div class="card">
                @include($step['view'], [
                    'step' => $step,
                    'active' => $index === $this->activeKey,
                    'iteration' => $loop->iteration,
                    'total' => count($steps)
                ])
            </div>
        @endforeach
        </div>
    </div>
</div>
