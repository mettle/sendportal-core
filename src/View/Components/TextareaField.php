<?php

declare(strict_types=1);

namespace Sendportal\Base\View\Components;

use Illuminate\View\Component;

class TextareaField extends Component
{
    /** @var string */
    public $name;

    /** @var string */
    public $label;

    /**
     * Create the component instance.
     *
     * @param  string  $name
     * @param  string  $label
     * @return void
     */
    public function __construct(string $name, string $label = '')
    {
        $this->name = $name;
        $this->label = $label;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('sendportal::components.textarea-field');
    }
}
