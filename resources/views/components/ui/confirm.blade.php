@props([
    'id' => null,
    'title' => 'Confirm',
    'message' => null,
    'confirmText' => 'Confirm',
    'cancelText' => 'Cancel',
    'variant' => 'danger',
    'size' => 'sm',
    'staticBackdrop' => true,
    'icon' => 'mdi mdi-alert-outline',
])

@php
  // Fallback id so "Undefined variable $id" never happens
  $modalId = $id ?: 'confirm-'.\Illuminate\Support\Str::uuid();
@endphp

<div class="modal fade"
     id="{{ $modalId }}"
     tabindex="-1"
     aria-hidden="true"
     wire:ignore.self
     data-bs-backdrop="{{ $staticBackdrop ? 'static' : 'true' }}"
     data-bs-keyboard="{{ $staticBackdrop ? 'false' : 'true' }}">
  <div class="modal-dialog modal-{{ $size }} modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">
          @if ($icon)<i class="{{ $icon }} me-1"></i>@endif
          {{ $title }}
        </h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        @if ($message)
          <p class="mb-0">{{ $message }}</p>
        @endif
        {{ $slot }}
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ $cancelText }}</button>

        @isset($confirm)
          {{ $confirm }}
        @else
          <button type="button" class="btn btn-{{ $variant }} text-light waves-effect waves-light" data-bs-dismiss="modal">
            {{ $confirmText }}
          </button>
        @endisset
      </div>
    </div>
  </div>
</div>
