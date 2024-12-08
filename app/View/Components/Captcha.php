<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Captcha extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */

    public $path;
    public $class;

    public function __construct($path = null, $class = null)
    {
        $this->path = $path;
        $this->class = $class;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $class = $this->class;

        if ($this->path) {
            return view($this->path . '.captcha');
        }

        return view('partials.captcha', compact('class'));
    }
}
