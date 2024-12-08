<?php

namespace App\View\Components;

use App\Models\Form;
use Illuminate\View\Component;

class ViserForm extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */

    public $identifier;
    public $identifierValue;
    public $currency;
    public $form;
    public $formData;

    public function __construct($identifier, $identifierValue, $currency = '')
    {
        $this->identifier = $identifier;
        $this->identifierValue = $identifierValue;
        $this->currency = $currency;
        $this->form = Form::where($this->identifier, $this->identifierValue)->first();
        $this->formData = @$this->form->form_data ?? [];
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.viser-form');
    }
}
