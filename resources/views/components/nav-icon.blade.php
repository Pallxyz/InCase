@props(['name'])

@switch($name)
    @case('squares-2x2')
        <x-icon.squares-2x2 {{ $attributes }} />
        @break
    @case('cube')
        <x-icon.cube {{ $attributes }} />
        @break
    @case('calendar-days')
        <x-icon.calendar-days {{ $attributes }} />
        @break
    @case('calendar')
        <x-icon.calendar {{ $attributes }} />
        @break
    @case('clock')
        <x-icon.clock {{ $attributes }} />
        @break
    @case('academic-cap')
        <x-icon.academic-cap {{ $attributes }} />
        @break
    @case('identification')
        <x-icon.identification {{ $attributes }} />
        @break
    @case('bell')
        <x-icon.bell {{ $attributes }} />
        @break
@endswitch
