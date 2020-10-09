<?php

namespace Sendportal\Base\Providers;

use Form;
use Illuminate\Support\ServiceProvider;

use Session;

class FormServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Form::macro('textField', function ($name, $label = null, $value = null, $attributes = []) {
            $element = Form::text($name, $value, Form::fieldAttributes($name, $attributes));

            return Form::fieldWrapper($name, $label, $element);
        });

        Form::macro('passwordField', function ($name, $label = null, $attributes = []) {
            $element = Form::password($name, Form::fieldAttributes($name, $attributes));

            return Form::fieldWrapper($name, $label, $element);
        });

        Form::macro('textareaField', function ($name, $label = null, $value = null, $attributes = []) {
            $element = Form::textarea($name, $value, Form::fieldAttributes($name, $attributes));

            return Form::fieldWrapper($name, $label, $element);
        });

        Form::macro('fileField', function ($name, $label = null, $attributes = []) {
            $element = Form::file($name, Form::fieldAttributes($name, $attributes));

            return Form::fieldWrapper($name, $label, $element);
        });

        Form::macro('selectField', function ($name, $label = null, $options, $value = null, $attributes = []) {
            $element = Form::select($name, $options, $value, Form::fieldAttributes($name, $attributes));

            return Form::fieldWrapper($name, $label, $element);
        });

        Form::macro('selectMultipleField', function ($name, $label = null, $options, $value = null, $attributes = []) {
            $attributes = array_merge($attributes, ['multiple' => true, 'class' => 'selectpicker']);
            $element = Form::select($name, $options, $value, Form::fieldAttributes($name, $attributes));

            return Form::fieldWrapper($name, $label, $element);
        });

        Form::macro('selectRangeField', function ($name, $label = null, $begin, $end, $value = null, $attributes = []) {
            $range = array_combine($range = range($begin, $end), $range);

            $element = Form::select($name, $range, $value, Form::fieldAttributes($name, $attributes));

            return Form::fieldWrapper($name, $label, $element);
        });

        Form::macro('selectMonthField', function ($name, $label = null, $value = null, $attributes = []) {
            $months = [];

            foreach (range(1, 12) as $month) {
                $months[$month] = strftime('%B', mktime(0, 0, 0, $month, 1));
            }

            $element = Form::select($name, $months, $value, Form::fieldAttributes($name, $attributes));

            return Form::fieldWrapper($name, $label, $element);
        });

        Form::macro('checkboxField', function ($name, $label = null, $value = 1, $checked = null, $attributes = []) {
            $attributes = array_merge(['id' => 'id-field-' . $name], $attributes);
            $element = Form::checkbox($name, $value, $checked, $attributes);

            return Form::fieldWrapper($name, $label, $element);
        });

        Form::macro('switchField', function ($name, $label = null, $value = 1, $checked = null, $attributes = []) {
            $attributes = array_merge(['id' => 'id-field-' . $name], $attributes);
            $element = Form::checkbox($name, $value, $checked, $attributes);

            return Form::fieldWrapper($name, $label, $element, ' custom-switch');
        });

        Form::macro('submitButton', function ($label = 'Save', array $params = []) {
            $defaults = [
                'class' => 'btn btn-primary'
            ];

            $attr = $params + $defaults;
            $res = [];

            foreach ($attr as $key => $val) {
                $res[] = e($key).'="'.e($val).'"';
            }

            $out = '<div class="form-group row">';
            $out .= '<div class="offset-sm-3 col-sm-9">';
            $out .= '<button type="submit" '.implode(' ', $res).'>' . $label . '</button>';
            $out .= '</div>';
            $out .= '</div>';

            return $out;
        });

        Form::macro('fieldWrapper', function ($name, $label, $element, $wrapperClass = '') {
            $out = '<div class="form-group row form-group-' . $name . $wrapperClass;
            $out .= Form::fieldError($name) . '">';
            $out .= Form::fieldLabel($name, $label);
            $out .= '<div class="col-sm-9">';
            $out .= $element;
            $out .= '</div>';
            $out .= '</div>';

            return $out;
        });

        Form::macro('fieldError', function ($field) {
            $error = '';

            if ($errors = session('errors')) {
                $error = $errors->first($field) ? ' has-error' : '';
            }

            return $error;
        });

        Form::macro('fieldErrorMessage', function ($field) {
            $error = '';

            if ($errors = session('errors')) {
                $error = $errors->first($field);
            }

            return $error;
        });

        Form::macro('fieldLabel', function ($name, $label) {
            if (is_null($label)) {
                return '';
            }

            $name = str_replace('[]', '', $name);

            $out = '<label for="id-field-' . $name . '" class="control-label col-sm-3">';
            $out .= $label . '</label>';

            return $out;
        });

        Form::macro('fieldAttributes', function ($name, $attributes = []) {
            $name = str_replace('[]', '', $name);

            $class = 'form-control';
            if (\Arr::get($attributes, 'class')) {
                $class .= ' ' . \Arr::get($attributes, 'class');
            }

            $attributes['class'] = $class;

            return array_merge(['id' => 'id-field-' . $name], $attributes);
        });
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
