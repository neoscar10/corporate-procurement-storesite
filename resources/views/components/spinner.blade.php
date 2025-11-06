{{-- resources/views/components/spinner.blade.php --}}
@props([
  'size' => 'sm',     // sm|md|lg
  'type' => 'border', // border|grow
  'text' => null,
  'class' => '',
])

@php
  $isGrow = $type === 'grow';
  $base = $isGrow ? 'spinner-grow' : 'spinner-border';
  $sizeClass = $size === 'sm' ? $base.'-sm' : ''; // Bootstrap has only -sm
@endphp

<span {{ $attributes->merge(['class' => 'd-inline-flex align-items-center gap-2 '.$class]) }}>
  <span class="{{ $base }} {{ $sizeClass }}" role="status" @if($size==='lg') style="width:2.25rem;height:2.25rem" @endif>
    <span class="visually-hidden">Loading…</span>
  </span>
  @if($text)
    <span>{{ $text }}</span>
  @endif
</span>
