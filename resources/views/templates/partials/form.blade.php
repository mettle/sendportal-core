<div class="form-group row form-group-name">
    <label for="id-field-name" class="control-label col-sm-2">{{ __('Template Name') }}</label>
    <div class="col-sm-6">
        <input id="id-field-name" class="form-control" name="name" type="text" value="{{ old('name', $template->name ?? '') }}">
    </div>
</div>

@include('sendportal::templates.partials.editor')

<div class="form-group row">
    <div class="offset-2 col-10">
        <a href="#" class="btn btn-md btn-secondary btn-preview">{{ __('Show Preview') }}</a>
        <button class="btn btn-primary btn-md" type="submit">{{ __('Save Template') }}</button>
    </div>
</div>