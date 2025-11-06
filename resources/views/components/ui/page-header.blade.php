@props([
    'title' => 'Page',
    'subtitle' => null,
])  

    <div     class="d-flex
         align-items-center justify-content-between mb-3">
    <div>
      <h4 class="mb-0">{{ $title }}</h4>
        @if($subtitle)<p class="text-muted mb-0">{{ $subtitle }}</p>@endif
    </div>
  <div class="d-flex gap-2">
    {{ $actions ?? '' }}
  </div>
</div>
<hr class="mt-3 mb-4">
