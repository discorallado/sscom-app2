<x-tables::row>
  <x-tables::cell>
    {{-- for the checkbox column --}}
  </x-tables::cell>

  @foreach ($columns as $column)
  <x-tables::cell>
    @if (array_key_exists($column->getName(), $calc_columns))

    <div class="filament-tables-column-wrapper">
      <div class="filament-tables-text-column">
        <div class="inline-flex items-center">
          <span class="font-medium">
            @switch($calc_columns[$column->getName()])
            @case('money_clp')
            ${{ number_format($records->sum($column->getName()),0,'','.') }}
            @endswitch
          </span>
        </div>
      </div>
    </div>

    @endif

  </x-tables::cell>
  @endforeach

</x-tables::row>
