<?php

declare(strict_types=1);

namespace Sendportal\Base\View\Components;

use Illuminate\View\Component;

class SubmitButton extends Component
{
    /** @var string */
    public $label;

    /**
     * Create the component instance.
     *
     * @param  string  $label
     * @return void
     */
    public function __construct(string $label)
    {
        $this->label = $label;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|\Closure|string
     */
    public function render()
    {
        return view('sendportal::components.submit-button');
    }
}