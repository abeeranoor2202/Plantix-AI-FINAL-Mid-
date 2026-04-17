{{ strip_tags($greeting ?? '') }}

@foreach ($introLines as $line)
{{ strip_tags($line) }}

@endforeach
@if (! empty($actionText) && ! empty($actionUrl))
{{ strip_tags($actionText) }}: {{ $actionUrl }}

@endif
@foreach ($outroLines as $line)
{{ strip_tags($line) }}

@endforeach
@if (! empty($salutation))
{{ strip_tags($salutation) }}
@else
Regards,
{{ config('app.name') }}
@endif
