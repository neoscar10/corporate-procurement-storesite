@props([
    'label' => null,
    'name' => null,
    'rows' => 4,
    'required' => false,
    'help' => null,
])
 
<label class="form-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
<textarea {{ $attributes->merge([
    'class' => 'form-control',
    'name' => $name,
    'rows' => $rows
]) }}></textarea>

@error($name)<div class="text-danger small mt-1">{{ $message }}</div>@enderror
@if($help)<div class="form-text">{{ $help }}</div>@endif
