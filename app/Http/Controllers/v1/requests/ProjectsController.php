<?php

namespace App\Http\Controllers\v1\requests;

use App\Http\Controllers\v1\Controller;
use App\Models\Project;
use App\Models\ProjectLike;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use function env;
use function response;

class ProjectsController extends Controller
{
    public function userProjects($username): JsonResponse
    {
        $user = User::where('name', $username)->firstOrFail();
        $projects = Project::with('client', 'worker', 'serviceRequest')->where('client_id', $user->id)->orWhere('client_id', $user->id)->paginate();
        return response()->json($projects);
    }

    public function createProject(Request $request, $requestID): JsonResponse
    {
        $request->validate([
            'name' => 'required|string',
            'file' => 'required'
        ]);

        $user = $request->user();
        $serviceRequest = ServiceRequest::where('id', $requestID)->firstOrFail();

        $allowed_mimes = ['image/png', 'image/jpg', 'image/jpeg'];
        $file_locations = [];

        if ($serviceRequest->client_id != $user->id) {
            return response()->json([
                'error' => 'Only request client can do this action.'
            ], 403);
        }

        if ($request->hasFile('file')) {
            foreach ($request->file as $file) {
                if (in_array($file->getClientMimeType(), $allowed_mimes)) {
                    // Storing File in Amazon s3
                    $fileExtension = $file->getClientOriginalExtension();
                    $fileName = Str::random(32) . '.' . $fileExtension;
                    $file->storeAs('project_files/', $fileName, 's3');

                    $fileLocation = 'https://' . env('AWS_BUCKET') . '.' . 's3' . '.' . env('AWS_DEFAULT_REGION') . '.amazonaws.com/project_files/' . $fileName;

                    $file_locations[] = $fileLocation;
                } else {
                    return response()->json([
                        'error' => 'File type is not accepted, We only accept png, jpg, jpeg.'
                    ], 415);
                }
            }
        }

        $json_file_names = json_encode($file_locations, JSON_UNESCAPED_SLASHES);

        $project = new Project;
        $project->files = $json_file_names;
        $project->name = $request->name;
        $project->request_id = $serviceRequest->id;
        $project->client_id = $serviceRequest->client_id;
        $project->worker_id = $serviceRequest->worker_id;
        $project->save();

        return response()->json($project);
    }

    public function likeProject(Request $request, $projectID): JsonResponse
    {
        $user = $request->user();
        $project = Project::where('id', $projectID)->firstOrFail();

        $projectLike = ProjectLike::where('user_id', $user->id)->where('project_id', $project->id)->first();

        if ($projectLike) {
            $projectLike->delete();

            return response()->json([
              'message' => 'Unliked project successfully.'
            ]);
        }

        $like = new ProjectLike;
        $like->project_id = $project->id;
        $like->user_id = $user->id;
        $like->save();

        return response()->json($like);
    }
}
