@props([
  'label' => null,
  'name' => null,
  'type' => 'text',
  'required' => false,
  'help' => null,
  'icon' => null,
  'placeholder' => null,
  'toggle' => false,   // enable eye toggle for password fields
])

@php
  // Derive field name from wire:model.* when name not passed
  $wireField = null;
  foreach (['wire:model','wire:model.defer','wire:model.blur','wire:model.live'] as $k) {
    if ($attributes->has($k)) { $wireField = $attributes->get($k); break; }
  }
  $field = $name ?? $wireField;

  // Stable ID for the input (needed for the toggle JS)
  $id = $attributes->get('id') ?? ($field ? str_replace(['.', '[', ']'], '_', $field) . '_input' : 'input_'.uniqid());
@endphp

@if($label)
  <label class="form-label" for="{{ $id }}">
    {{ $label }} @if($required)<span class="text-danger">*</span>@endif
  </label>
@endif

{{-- Password with seamless eye toggle (no button hover bg) --}}
@if($type === 'password' && $toggle)
  <div class="input-group">
    @if($icon)
      <span class="input-group-text"><i class="{{ $icon }}"></i></span>
    @endif

    <input
      id="{{ $id }}"
      {{ $attributes->merge([
        'class' => 'form-control',
        'type' => 'password',
        'name' => $field,
        'placeholder' => $placeholder,
        $required ? 'required' : null => true,
      ]) }}
    >

    {{-- Use input-group-text so it visually matches the input --}}
    <span
      class="input-group-text bg-transparent"
      style="cursor:pointer; user-select:none"
      aria-label="Toggle password visibility"
      title="Show/Hide password"
      onclick="(function(el){
        var i = el.querySelector('i');
        var input = document.getElementById('{{ $id }}');
        if(!input) return;
        if(input.type === 'password'){
          input.type = 'text';
          i.classList.remove('mdi-eye-off-outline');
          i.classList.add('mdi-eye-outline');
        } else {
          input.type = 'password';
          i.classList.remove('mdi-eye-outline');
          i.classList.add('mdi-eye-off-outline');
        }
      })(this)"
    >
      <i class="mdi mdi-eye-off-outline"></i>
    </span>
  </div>

{{-- Non-toggle variants (with/without left icon) --}}
@elseif($icon)
  <div class="input-group">
    <span class="input-group-text"><i class="{{ $icon }}"></i></span>
    <input id="{{ $id }}" {{ $attributes->merge([
      'class' => 'form-control',
      'type' => $type,
      'name' => $field,
      'placeholder' => $placeholder,
      $required ? 'required' : null => true,
    ]) }}>
  </div>
@else
  <input id="{{ $id }}" {{ $attributes->merge([
    'class' => 'form-control',
    'type' => $type,
    'name' => $field,
    'placeholder' => $placeholder,
    $required ? 'required' : null => true,
  ]) }}>
@endif

@isset($field)
  @error($field)
    <div class="invalid-feedback d-block">{{ $message }}</div>
  @enderror
@endisset

@if($help)<div class="form-text">{{ $help }}</div>@endif
