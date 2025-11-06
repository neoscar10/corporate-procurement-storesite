@props([
  'headers' => [], // ['Name','Email',...]
])

<div class="table-responsive">
  <table class="table align-middle table-nowrap table-sm">
    @if(!empty($headers))
      <thead class="table-light">
        <tr>
          @foreach($headers as $th)
            <th scope="col">{{ $th }}</th>
          @endforeach
        </tr>
      </thead>
    @endif

    <tbody>
      {{ $slot }}
    </tbody>
  </table>
</div>
