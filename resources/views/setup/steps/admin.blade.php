<div class="card-header {{ $step['completed'] ? 'bg-success text-white' : ($active ? 'bg-light' : null) }}">
    <h6 class="mb-0">
        {{ $step['completed'] ? '✔' : ($active ? '➡️' : null) }} {{ $step['name'] }}
        <span class="text-small float-right"><em>{{ $iteration }}/{{ $total }}</em></span>
    </h6>
</div>
<div class="collapse {{ $active ? 'show' : null }}">
    <div class="card-body">
        @if ($step['completed'])
            <p>✔️ Admin user account exists</p>
            <a href="{{ route('login') }}" class="btn btn-primary btn-sm">Login</a>
        @else
            <form wire:submit.prevent="run(Object.fromEntries(new FormData($event.target)))">
                <div class="form-group">
                    <label for="company">Company/Workspace name</label>
                    <input type="text" class="form-control" id="company" name="company" required>
                    @error('company') <span class="form-text text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                    @error('name') <span class="form-text text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                    @error('email') <span class="form-text text-danger">{{ $message }}</span>@enderror
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    @error('password') <span class="form-text text-danger">{{ $message }}</span>@enderror
                </div>
                <button class="btn btn-primary btn-sm" type="submit" wire:loading.attr="disabled">
                    <span wire:loading class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Submit
                </button>
            </form>
        @endif
    </div>
</div>