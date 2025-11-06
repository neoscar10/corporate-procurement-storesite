@props([
    'id',
    'title' => '',
    'size' => 'lg',        // sm|lg|xl|fullscreen
    'centered' => true,
    'scrollable' => false,
    'staticBackdrop' => true,
])

<div class="modal fade"
     id="{{ $id }}"
     tabindex="-1"
     aria-hidden="true"
     wire:ignore.self
     data-bs-backdrop="{{ $staticBackdrop ? 'static' : 'true' }}"
     data-bs-keyboard="{{ $staticBackdrop ? 'false' : 'true' }}">
  <div class="modal-dialog {{ $scrollable ? 'modal-dialog-scrollable' : '' }} modal-{{ $size }} {{ $centered ? 'modal-dialog-centered' : '' }}">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ $title }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        {{ $slot }}
      </div>

      @isset($footer)
        <div class="modal-footer">
          {{ $footer }}
        </div>
      @endisset
    </div>
  </div>
</div>
