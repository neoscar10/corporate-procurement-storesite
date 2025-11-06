@props([
  'steps' => [],
  'current' => 1,
])

@php
  // state: done|current|todo
  $stateFor = function(array $s, int $idx) use ($current) {
    if (($s['state'] ?? null) === 'done' || $idx < $current) return 'done';
    if ($idx === $current || ($s['state'] ?? null) === 'current') return 'current';
    return 'todo';
  };
@endphp

<div class="wizard-stepper mb-3">
  <div class="wizard-track row gap-2">
    @foreach($steps as $i => $s)
      @php
        $idx = $i + 1;
        $state = $stateFor($s, $idx);
      @endphp

      <div class="step-item col flex-fill">
        <div class="step-label text-muted {{ $state==='current' ? 'text-primary' : '' }} fs-11 mb-1 text-truncate w-100">
          {{ $s['label'] ?? ('Step '.$idx) }}
        </div>

        <div class="d-flex align-items-center w-100">
          <span class="step-circle rounded-circle
            @if($state==='done') bg-success text-white
            @elseif($state==='current') bg-primary text-white
            @else bg-light text-muted
            @endif">
            {{ $idx }}
          </span>

          @if (!$loop->last)
            <span class="wizard-connector flex-grow-1 mx-1 border-top
              {{ ($state==='done') ? 'border-success' : 'border-muted' }}" style="opacity:.45"></span>
          @endif
        </div>
      </div>
    @endforeach
  </div>
</div>

@once
  @push('styles')
    <style>
      /* Single-row, compact stepper within card */
      .wizard-stepper { overflow: hidden; }
      .wizard-track   { flex-wrap: nowrap; min-width: 100%; }
      .step-item      { min-width: 0; }             /* allow shrinking */
      .step-label     { max-width: 100%; }          /* truncate nicely */
      .step-circle    {
        width: 28px; height: 28px;
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 600; font-size: .75rem;
      }
      .fs-11 { font-size: .6875rem; } /* a bit smaller than fs-12 */
      /* No wrapping on small screens; rely on shrink/truncate */
      @media (max-width: 576px){
        .step-circle { width: 26px; height: 26px; font-size: .7rem; }
      }
    </style>
  @endpush
@endonce
