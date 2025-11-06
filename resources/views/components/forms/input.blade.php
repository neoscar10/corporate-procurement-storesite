@props([
  'label' => null,
  'name' => null,
  'type' => 'text',
  'required' => false,
  'help' => null,
  'icon' => null,
  'placeholder' => null,
])

@php
  // Derive field name from wire:model.* when name not passed
  $wireField = null;
  foreach (['wire:model','wire:model.defer','wire:model.blur','wire:model.live'] as $k) {
    if ($attributes->has($k)) { $wireField = $attributes->get($k); break; }
  }
  $field = $name ?? $wireField;
@endphp

@if($label)
  <label class="form-label">
    {{ $label }} @if($required)<span class="text-danger">*</span>@endif
  </label>
@endif

@if($icon)
  <div class="input-group">
    <span class="input-group-text"><i class="{{ $icon }}"></i></span>
    <input {{ $attributes->merge([
      'class' => 'form-control',
      'type' => $type,
      'name' => $field,
      'placeholder' => $placeholder,
      $required ? 'required' : null => true,
    ]) }}>
  </div>
@else
  <input {{ $attributes->merge([
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
