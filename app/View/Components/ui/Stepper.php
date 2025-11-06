<?php

namespace App\View\Components\Ui;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Stepper extends Component
{
    public function __construct(
        public array $steps = [],     // [['label'=>'Basic Info','state'=>'done|current|todo']]
        public ?int $current = null,  // 1-based
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.ui.stepper');
    }
}
