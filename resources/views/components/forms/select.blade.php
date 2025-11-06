@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'help' => null,
])

<label class="form-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
<select {{ $attributes->merge(['class' => 'form-select', 'name' => $name]) }}>
  {{ $slot }}
</select>

@error($name)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
@if($help)<div class="form-text">{{ $help }}</div>@endif
