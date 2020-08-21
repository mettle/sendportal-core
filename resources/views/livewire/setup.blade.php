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
                <div class="card-header {{ $step['completed'] ? 'bg-success text-white' : ($index === $this->activeKey ? 'bg-light' : null) }}">
                    <h6 class="mb-0">
                        {{ $step['completed'] ? '✔' : ($index === $this->activeKey ? '➡️' : null) }} {{ $step['name'] }}
                        <span class="text-small float-right"><em>{{ $loop->iteration }}/{{ count($steps) }}</em></span>
                    </h6>
                </div>
                <div class="collapse {{ $index === $this->activeKey ? 'show' : null }}">
                    <div class="card-body">
                        @include($step['view'], [
                            'completed' => $step['completed']
                        ])
                    </div>
                </div>
            </div>
        @endforeach
        </div>
    </div>
</div>
