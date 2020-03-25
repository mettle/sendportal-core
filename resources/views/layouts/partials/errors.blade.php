@if (isset($errors) and $errors->any())
    <div class="row">
        <div class="col-lg-6 offset-lg-3">
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif
