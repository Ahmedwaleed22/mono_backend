<?php

namespace App\Http\Controllers\v1\admin\requests;

use App\Http\Controllers\v1\admin\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $projects = Project::with(['client', 'worker'])->get();
        return response()->json($projects);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $project = Project::with(['client', 'worker'])->where('id', $id)->firstOrFail();
        return response()->json($project);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $project = Project::where('id', $id)->firstOrFail();
        $project->delete();

        return response()->json([
            'message' => 'Successfully deleted project.'
        ], 202);
    }
}
