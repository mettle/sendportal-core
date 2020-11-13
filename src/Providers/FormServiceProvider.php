<?php

namespace Sendportal\Base\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Sendportal\Base\View\Components\CheckboxField;
use Sendportal\Base\View\Components\FieldWrapper;
use Sendportal\Base\View\Components\FileField;
use Sendportal\Base\View\Components\Label;
use Sendportal\Base\View\Components\SelectField;
use Sendportal\Base\View\Components\SubmitButton;
use Sendportal\Base\View\Components\TextareaField;
use Sendportal\Base\View\Components\TextField;

class FormServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Blade::component(TextField::class, 'sendportal.text-field');
        Blade::component(TextareaField::class, 'sendportal.textarea-field');
        Blade::component(FileField::class, 'sendportal.file-field');
        Blade::component(SelectField::class, 'sendportal.select-field');
        Blade::component(CheckboxField::class, 'sendportal.checkbox-field');
        Blade::component(Label::class, 'sendportal.label');
        Blade::component(SubmitButton::class, 'sendportal.submit-button');
        Blade::component(FieldWrapper::class, 'sendportal.field-wrapper');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
    }
}
