@extends('layouts.medicontrol')
@section('title', 'Documentos del paciente')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/medicontrol-fase3.css') }}">
@endpush

@section('content')
@php
    $puedeCargar = in_array(auth()->user()->rol, ['admin', 'recepcionista'], true);
    $puedeEliminar = auth()->user()->rol === 'admin';
@endphp
<div class="mc-page-head d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <span class="mc-eyebrow">Expediente digital / Paciente #{{ $paciente->paciente_id }}</span>
        <h1 class="mc-page-title mb-1">Documentos de {{ $paciente->nombre }}</h1>
        <p class="mc-muted mb-0">Recetas, resultados de exámenes y documentos adjuntos.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if($puedeCargar)
            <a href="{{ route('pacientes.show', $paciente->paciente_id) }}" class="btn btn-outline-secondary">Volver a la ficha</a>
        @else
            <a href="{{ route('paciente.mis-citas') }}" class="btn btn-outline-secondary">Volver a mis citas</a>
        @endif
    </div>
</div>

@if($puedeCargar)
<section class="mc-card mc-f3-card mb-4" aria-labelledby="subir-documento-titulo">
    <div class="mc-card-head"><h2 class="mc-small-title mb-0" id="subir-documento-titulo">Subir nuevo documento</h2></div>
    <div class="mc-card-body">
        <form method="POST" action="{{ route('documentos.store') }}" enctype="multipart/form-data" class="row g-3" data-mc-file-form>
            @csrf
            <input type="hidden" name="paciente_id" value="{{ $paciente->paciente_id }}">
            <div class="col-md-4">
                <label for="tipo" class="form-label fw-semibold">Tipo de documento <span class="text-danger">*</span></label>
                <select id="tipo" name="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                    <option value="">Seleccionar tipo</option>
                    <option value="receta" @selected(old('tipo') === 'receta')>Receta médica</option>
                    <option value="examen" @selected(old('tipo') === 'examen')>Resultado de examen</option>
                    <option value="otro" @selected(old('tipo') === 'otro')>Otro documento</option>
                </select>
                @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-8">
                <label for="archivo" class="form-label fw-semibold">Archivo <span class="text-danger">*</span></label>
                <input id="archivo" name="archivo" type="file" class="form-control @error('archivo') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" required data-max-bytes="5242880" aria-describedby="archivo-ayuda">
                <div id="archivo-ayuda" class="form-text" role="status" aria-live="polite">PDF, JPG o PNG. Peso máximo: 5 MB.</div>
                @error('archivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12 d-flex flex-wrap align-items-center gap-2 justify-content-between">
                <p class="mc-muted mb-0 small">Se guardará en el expediente del paciente seleccionado.</p>
                <button type="submit" class="btn btn-primary" data-mc-submit>Subir documento</button>
            </div>
        </form>
    </div>
</section>
@endif

<section class="mc-card mc-f3-card" aria-labelledby="listado-documentos-titulo">
    <div class="mc-card-head d-flex justify-content-between flex-wrap align-items-center gap-2">
        <h2 class="mc-small-title mb-0" id="listado-documentos-titulo">Documentos disponibles</h2>
        <span class="mc-f3-count">{{ $documentos->count() }} {{ $documentos->count() === 1 ? 'archivo' : 'archivos' }}</span>
    </div>
    <div class="mc-card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mc-f3-table mb-0">
                <thead><tr><th scope="col">Documento</th><th scope="col">Tipo</th><th scope="col">Fecha</th><th scope="col">Tamaño</th><th scope="col" class="text-end">Acciones</th></tr></thead>
                <tbody>
                @forelse($documentos as $documento)
                    <tr>
                        <td class="mc-f3-filename" title="{{ $documento->nombre_original }}"><span class="mc-f3-file-icon" aria-hidden="true">{{ strtolower(pathinfo($documento->nombre_original, PATHINFO_EXTENSION)) === 'pdf' ? 'PDF' : 'IMG' }}</span> {{ $documento->nombre_original }}</td>
                        <td><span class="mc-f3-tag">{{ ['receta' => 'Receta', 'examen' => 'Examen', 'otro' => 'Otro'][$documento->tipo] ?? ucfirst($documento->tipo) }}</span></td>
                        <td>{{ $documento->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}</td>
                        <td>{{ $documento->peso_kb ? number_format($documento->peso_kb, 0, ',', '.') . ' KB' : '—' }}</td>
                        <td>
                            <div class="d-flex flex-wrap justify-content-end gap-2">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('documentos.descargar', $documento->getKey()) }}">Descargar</a>
                                @if($puedeEliminar)
                                    <form method="POST" action="{{ route('documentos.destroy', $documento->getKey()) }}" data-mc-delete-form>
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-5 mc-muted">No hay documentos registrados para este paciente.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/medicontrol-fase3.js') }}" defer></script>
@endpush
