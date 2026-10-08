<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    const HORA_APERTURA    = 8;
    const HORA_CIERRE      = 17;
    const MINUTOS_POR_CITA = 30;
    const HORAS_MINIMAS_PARA_CANCELAR = 12;

    const ESTADOS_LIBERAN = ['Cancelada', 'No-show'];

    public function index()
    {
        $usuario = auth()->user();

        $consulta = Appointment::with(['patient', 'doctor'])
            ->orderBy('fecha', 'desc')
            ->orderBy('hora', 'desc');

        if ($usuario->rol === 'paciente') {
            $paciente = $this->pacienteDelUsuario();

            if (!$paciente) {
                return view('citas.index', ['citas' => collect()]);
            }

            $consulta->where('paciente_id', $paciente->paciente_id);
        }

        $citas = $consulta->paginate(20);

        return view('citas.index', compact('citas'));
    }

    public function agenda(Request $request)
    {
        $fecha = $request->input('fecha', now()->toDateString());

        $citas = Appointment::with(['patient', 'doctor'])
            ->whereDate('fecha', $fecha)
            ->orderBy('hora')
            ->get();

        return view('recepcionista.agenda', compact('citas', 'fecha'));
    }

    public function misCitas()
    {
        $paciente = $this->pacienteDelUsuario();

        $citas = $paciente
            ? Appointment::with('doctor')
                ->where('paciente_id', $paciente->paciente_id)
                ->orderBy('fecha', 'desc')
                ->orderBy('hora', 'desc')
                ->get()
            : collect();

        return view('paciente.mis-citas', compact('citas', 'paciente'));
    }

    public function create()
    {
        $doctores = Doctor::orderBy('nombre')->get();

        $pacientes = auth()->user()->rol === 'paciente'
            ? collect()
            : Patient::orderBy('nombre')->get();

        return view('citas.create', compact('doctores', 'pacientes'));
    }

    public function store(Request $request)
    {
        $usuario = auth()->user();

        $datos = $request->validate([
            'doctor_id'   => 'required|exists:doctors,doctor_id',
            'fecha'       => 'required|date',
            'hora'        => 'required',
            'razon'       => 'required|string|max:500',
            'paciente_id' => 'nullable|exists:patients,paciente_id',
        ], [
            'doctor_id.required' => 'Debe seleccionar una doctora.',
            'fecha.required'     => 'Debe indicar la fecha de la cita.',
            'hora.required'      => 'Debe indicar la hora de la cita.',
            'razon.required'     => 'Debe indicar el motivo de la consulta.',
        ]);

        $fechaHora = Carbon::parse($datos['fecha'].' '.$datos['hora']);

        if ($fechaHora->isPast()) {
            return back()->withInput()->withErrors([
                'fecha' => 'No se puede agendar una cita en una fecha u hora que ya paso.'
            ]);
        }

        if ($fechaHora->hour < self::HORA_APERTURA || $fechaHora->hour >= self::HORA_CIERRE) {
            return back()->withInput()->withErrors([
                'hora' => 'El horario de atencion es de '.self::HORA_APERTURA.':00 a '.self::HORA_CIERRE.':00.'
            ]);
        }

        if ($fechaHora->isSunday()) {
            return back()->withInput()->withErrors([
                'fecha' => 'La clinica no atiende los domingos.'
            ]);
        }

        $ocupado = Appointment::where('doctor_id', $datos['doctor_id'])
            ->where('fecha', $datos['fecha'])
            ->where('hora', $datos['hora'])
            ->whereNotIn('status', self::ESTADOS_LIBERAN)
            ->exists();

        if ($ocupado) {
            return back()->withInput()->withErrors([
                'hora' => 'Ese horario ya esta ocupado para la doctora seleccionada. Elija otro.'
            ]);
        }

        if ($usuario->rol === 'paciente') {
            $paciente = $this->pacienteDelUsuario();

            if (!$paciente) {
                return back()->withErrors([
                    'paciente_id' => 'Su usuario no esta vinculado a una ficha de paciente. Comuniquese con la clinica.'
                ]);
            }

            $datos['paciente_id'] = $paciente->paciente_id;
            $datos['status']      = 'Pendiente';
        } else {
            if (empty($datos['paciente_id'])) {
                return back()->withInput()->withErrors([
                    'paciente_id' => 'Debe seleccionar un paciente.'
                ]);
            }

            $datos['status'] = 'Confirmado';
        }

        $yaTieneCita = Appointment::where('paciente_id', $datos['paciente_id'])
            ->where('fecha', $datos['fecha'])
            ->whereNotIn('status', self::ESTADOS_LIBERAN)
            ->exists();

        if ($yaTieneCita) {
            return back()->withInput()->withErrors([
                'fecha' => 'Este paciente ya tiene una cita registrada para ese dia.'
            ]);
        }

        Appointment::create($datos);

        return redirect()->route('citas.index')
                         ->with('exito', 'Cita agendada correctamente.');
    }

    public function show($id)
    {
        $cita = Appointment::with(['patient', 'doctor'])->findOrFail($id);

        if (auth()->user()->rol === 'paciente') {
            $paciente = $this->pacienteDelUsuario();

            if (!$paciente || $cita->paciente_id !== $paciente->paciente_id) {
                abort(403, 'No tiene permiso para ver esta cita.');
            }
        }

        return view('citas.show', compact('cita'));
    }

    public function cancelar(Request $request, $id)
    {
        $cita    = Appointment::findOrFail($id);
        $usuario = auth()->user();

        if (in_array($cita->status, ['Completada', 'Cancelada'])) {
            return back()->withErrors([
                'cancelar' => 'Esta cita ya no se puede cancelar.'
            ]);
        }

        if ($usuario->rol === 'paciente') {
            $paciente = $this->pacienteDelUsuario();

            if (!$paciente || $cita->paciente_id !== $paciente->paciente_id) {
                abort(403, 'No tiene permiso para cancelar esta cita.');
            }

            $fechaHora      = Carbon::parse($cita->fecha.' '.$cita->hora);
            $horasQueFaltan = now()->diffInHours($fechaHora, false);

            if ($horasQueFaltan < self::HORAS_MINIMAS_PARA_CANCELAR) {
                return back()->withErrors([
                    'cancelar' => 'Para cancelar debe hacerlo con al menos '.self::HORAS_MINIMAS_PARA_CANCELAR.' horas de anticipacion. Comuniquese con la clinica.'
                ]);
            }
        }

        $cita->update([
            'status'        => 'Cancelada',
            'observaciones' => $request->input('motivo_cancelacion', $cita->observaciones),
        ]);

        return redirect()->route('citas.index')
                         ->with('exito', 'Cita cancelada.');
    }

    public function cambiarEstado(Request $request, $id)
    {
        $cita = Appointment::findOrFail($id);

        $datos = $request->validate([
            'status'        => 'required|in:Pendiente,Confirmado,En proceso,Completada,Cancelada,No-show',
            'observaciones' => 'nullable|string',
        ]);

        $cambiosPermitidos = [
            'Pendiente'  => ['Confirmado', 'Cancelada'],
            'Confirmado' => ['En proceso', 'Cancelada', 'No-show'],
            'En proceso' => ['Completada'],
            'Completada' => [],
            'Cancelada'  => [],
            'No-show'    => [],
        ];

        if (!in_array($datos['status'], $cambiosPermitidos[$cita->status])) {
            return back()->withErrors([
                'status' => "No se puede pasar de '{$cita->status}' a '{$datos['status']}'."
            ]);
        }

        $cita->update($datos);

        return back()->with('exito', 'Estado de la cita actualizado.');
    }

    public function disponibilidad(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,doctor_id',
            'fecha'     => 'required|date',
        ]);

        $fecha = Carbon::parse($request->input('fecha'));

        if ($fecha->isSunday()) {
            return response()->json([]);
        }

        $ocupadas = Appointment::where('doctor_id', $request->input('doctor_id'))
            ->where('fecha', $fecha->toDateString())
            ->whereNotIn('status', self::ESTADOS_LIBERAN)
            ->pluck('hora')
            ->map(fn ($h) => substr($h, 0, 5))
            ->toArray();

        $libres = [];
        $hora   = $fecha->copy()->setTime(self::HORA_APERTURA, 0);
        $cierre = $fecha->copy()->setTime(self::HORA_CIERRE, 0);

        while ($hora < $cierre) {
            $etiqueta = $hora->format('H:i');

            if (!in_array($etiqueta, $ocupadas) && $hora->isFuture()) {
                $libres[] = $etiqueta;
            }

            $hora->addMinutes(self::MINUTOS_POR_CITA);
        }

        return response()->json($libres);
    }

    private function pacienteDelUsuario(): ?Patient
    {
        return Patient::where('email', auth()->user()->email)->first();
    }
}