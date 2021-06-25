<?php

namespace App\Http\Controllers;

use App\Models\Convalidacione;
use App\Models\ConvalidacionMateria;
use App\Models\Materia;
use App\Models\Materias;
use App\Models\MateriasPlan;
use App\Models\PlanEstudio;
use App\Models\Tecnologico;
use Illuminate\Http\Request;

/**
 * Class ConvalidacioneController
 * @package App\Http\Controllers
 */
class ConvalidacioneController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $convalidaciones = Convalidacione::join('plan_estudios as p', 'p.id', '=', 'convalidaciones.plan_externo')
            ->join('plan_estudios as p1', 'p1.id', '=', 'convalidaciones.plan_interno')
            ->join('tecnologicos as t', 't.id', '=', 'convalidaciones.tecnologico_procedente')
            ->join('tecnologicos as t1', 't1.id', '=', 'convalidaciones.tecnologico_receptor')
            ->select(['p.nombre as plan_externo', 'p1.nombre as plan_interno','t.nombre as tecnologico_procedente','t1.nombre as tecnologico_receptor','convalidaciones.id','convalidaciones.nombre_alumno'])->paginate();
        // var_dump($convalidaciones);
        // exit();

        return view('convalidacione.index', compact('convalidaciones'))
            ->with('i', (request()->input('page', 1) - 1) * $convalidaciones->perPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $convalidacione = new Convalidacione();
        $planes = PlanEstudio::all()->pluck('nombre', 'id');
        $tecnologicos = Tecnologico::all()->pluck('nombre', 'id');
        return view('convalidacione.create', compact('convalidacione', 'planes', 'tecnologicos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate(Convalidacione::$rules);

        $convalidacione = Convalidacione::create($request->all());

        return redirect()->route('convalidaciones.edit', ['convalidacione' => $convalidacione->id])
            ->with('success', 'Convalidacione created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $convalidacione = Convalidacione::find($id);

        return view('convalidacione.show', compact('convalidacione'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $planes = PlanEstudio::all()->pluck('nombre', 'id');
        $tecnologicos = Tecnologico::all()->pluck('nombre', 'id');
        $convalidacione = Convalidacione::find($id);
        $materia = new ConvalidacionMateria();
        $materias_cursadas = MateriasPlan::where('plan_id', $convalidacione->plan_externo)
            ->join('materias', 'materias.id', '=', 'materias_plan.materia_id')->select(['clave', 'nombre', 'id'])->get();
        $materias_convalidar = MateriasPlan::where('plan_id', $convalidacione->plan_interno)
            ->join('materias', 'materias.id', '=', 'materias_plan.materia_id')->select(['clave', 'nombre', 'id'])->get();
        $materias_convalidadas = ConvalidacionMateria::where('convalidacion_id', $convalidacione->id)->get();

        // $materias_cursadas= $materias_cursadas->pluck('clave','id');
        // $materias_convalidar=$materias_convalidar->pluck('clave','id');
        return view('convalidacione.edit', compact('convalidacione', 'materia', 'materias_cursadas', 'materias_convalidar', 'planes', 'tecnologicos', 'materias_convalidadas'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Convalidacione $convalidacione
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Convalidacione $convalidacione)
    {
        request()->validate(Convalidacione::$rules);

        $convalidacione->update($request->all());

        return redirect()->route('convalidaciones.index')
            ->with('success', 'Convalidacione updated successfully');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $convalidacione = Convalidacione::find($id)->delete();

        return redirect()->route('convalidaciones.index')
            ->with('success', 'Convalidacione deleted successfully');
    }
}
