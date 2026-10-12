@php
    // El controlador usa estos valores en la base de datos; aquí solo mejoramos su presentación.
    $etiquetas = [
        'Pendiente' => ['Solicitada', 'mc-status-pendiente'],
        'Confirmado' => ['Confirmada', 'mc-status-confirmado'],
        'En proceso' => ['En proceso', 'mc-status-proceso'],
        'Completada' => ['Completada', 'mc-status-completada'],
        'Cancelada' => ['Cancelada', 'mc-status-cancelada'],
        'No-show' => ['No asistió', 'mc-status-no-show'],
    ];
    $visual = $etiquetas[$estado] ?? [$estado, 'mc-status-completada'];
@endphp
<span class="mc-pill {{ $visual[1] }}">{{ $visual[0] }}</span>
