@props([
  'length' => 6,
  'name' => 'otp',
  'value' => '',
  'idPrefix' => 'otp_',
  'wireModel' => null,  // e.g. "code"
  'boxWidth' => '48px',
])

@php $length = (int) $length; @endphp

<label class="form-label">Verification Code</label>

{{-- Keep the dynamic inputs out of Livewire’s DOM diffing --}}
<div class="d-flex gap-2" role="group" aria-label="One-time passcode" wire:ignore>
  @for($i=0; $i<$length; $i++)
    <input
      type="text"
      inputmode="numeric"
      maxlength="1"
      class="form-control text-center"
      style="width: {{ $boxWidth }}"
      id="{{ $idPrefix.$i }}"
      aria-label="Digit {{ $i + 1 }}"
      oninput="otpOnInput(event, '{{ $idPrefix }}', {{ $length }})"
      onkeydown="otpOnKeyDown(event, '{{ $idPrefix }}', {{ $length }})"
      onpaste="otpOnPaste(event, '{{ $idPrefix }}', {{ $length }})"
    >
  @endfor
</div>

{{-- Bind only the hidden field to Livewire (deferred) --}}
<input
  type="hidden"
  name="{{ $name }}"
  id="{{ $idPrefix }}_hidden"
  @if($wireModel) wire:model.defer="{{ $wireModel }}" @endif
  value="{{ $value }}"
>

@error($wireModel ?? $name)<div class="text-danger small mt-1">{{ $message }}</div>@enderror

{{-- Helpers: update hidden input and notify Livewire without re-rendering the visible boxes --}}
<script>
function otpCollect(prefix, len){
  let v = '';
  for(let i=0; i<len; i++){
    const el = document.getElementById(prefix + i);
    v += (el && el.value) ? el.value : '';
  }
  const hidden = document.getElementById(prefix + '_hidden');
  if (hidden) {
    hidden.value = v;
    // Tell Livewire the bound input changed (deferred; will sync on next request)
    hidden.dispatchEvent(new Event('input', { bubbles: true }));
  }
}

function otpOnInput(e, prefix, len){
  const el = e.target;
  el.value = el.value.replace(/\D/g, '').slice(0,1);
  if (el.value) {
    const next = el.nextElementSibling;
    if (next && next.tagName === 'INPUT') next.focus();
  }
  otpCollect(prefix, len);
}

function otpOnKeyDown(e, prefix, len){
  const el = e.target;
  if (e.key === 'Backspace' && !el.value){
    const prev = el.previousElementSibling;
    if (prev && prev.tagName === 'INPUT'){ prev.focus(); prev.value=''; }
  }
  // Let DOM update, then collect
  setTimeout(() => otpCollect(prefix, len), 0);
}

function otpOnPaste(e, prefix, len){
  e.preventDefault();
  const text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'').slice(0,len);
  for(let i=0; i<len; i++){
    const el = document.getElementById(prefix + i);
    if (el) el.value = text[i] || '';
  }
  otpCollect(prefix, len);
}
</script>
