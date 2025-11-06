{{-- resources/views/components/ui/progress-loader.blade.php --}}
@props([
  'text' => 'Uploading…',
  'variant' => 'bar', // 'bar' | 'overlay'
  'height' => 8,
])

@php $isOverlay = $variant === 'overlay'; @endphp

<div
  x-data="{
    active:false, progress:0,
    init(){
      window.addEventListener('livewire-upload-start',   () => { this.active = true;  this.progress = 0 })
      window.addEventListener('livewire-upload-progress', e  => { this.progress = e.detail?.progress ?? this.progress })
      window.addEventListener('livewire-upload-error',    () => { this.active = false; this.progress = 0 })
      window.addEventListener('livewire-upload-finish',   () => { this.progress = 100; setTimeout(()=>{ this.active=false; this.progress=0 }, 300) })
    }
  }"
  x-cloak
  x-show="active"
  x-transition.opacity
  class="{{ $isOverlay ? 'position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-25 rounded z-10' : 'mb-2' }}"
>
  <div class="{{ $isOverlay ? 'card card-body py-3 px-4 shadow-sm' : 'card card-body py-2 px-3 mb-0 shadow-sm' }} w-100" style="max-width: 350px">
    <div class="d-flex align-items-center gap-2">
      <i class="mdi mdi-cloud-upload-outline fs-16 text-primary"></i>
      <span class="text-muted fs-12">{{ $text }}</span>
      <span class="ms-auto text-muted fs-12" x-text="progress + '%'"></span>
    </div>
    <div class="progress mt-2" style="height: {{ (int)$height }}px;">
      <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated"
           role="progressbar"
           :style="`width: ${progress}%;`"
           :aria-valuenow="progress" aria-valuemin="0" aria-valuemax="100"></div>
    </div>
  </div>
</div>

@once
  @push('styles')
    <style>
      [x-cloak]{display:none!important}
      .progress{border-radius:999px; overflow:hidden}
      .fs-12{font-size:.75rem}.fs-16{font-size:1rem}
      .z-10{z-index:10}
    </style>
  @endpush
@endonce
