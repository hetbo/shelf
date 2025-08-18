<?php

namespace Hetbo\Shelf\Http\Controllers\Api;

use Hetbo\Shelf\Http\Requests\AttachFileRequest;
use Hetbo\Shelf\Http\Requests\DetachFileRequest;
use Hetbo\Shelf\Http\Requests\ReorderFilesRequest;
use Hetbo\Shelf\Models\File;
use Hetbo\Shelf\Services\FileableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FileableController extends Controller
{
    public function __construct(
        private FileableService $fileableService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'fileable_type' => ['required', 'string'],
            'fileable_id' => ['required', 'integer'],
            'role' => ['sometimes', 'string'],
        ]);

        $attachments = $this->fileableService->getAttachments(
            $request->get('fileable_type'),
            $request->get('fileable_id'),
            $request->get('role')
        );

        return response()->json(['data' => $attachments]);
    }

    public function attach(AttachFileRequest $request): JsonResponse
    {
        $model = app($request->get('fileable_type'))->findOrFail($request->get('fileable_id'));

        $file = File::findOrFail($request->get('file_id'));

        $attachment = $this->fileableService->attach(
            $model,
            $file,
            $request->get('role'),
            $request->get('metadata', [])
        );

        return response()->json(['data' => $attachment], 201);
    }

    public function detach(DetachFileRequest $request): JsonResponse
    {
        $model = app($request->get('fileable_type'))->findOrFail($request->get('fileable_id'));
        $file = File::findOrFail($request->get('file_id'));

        $this->fileableService->detach($model, $file, $request->get('role'));

        return response()->json(['message' => 'File detached successfully']);
    }

    public function reorder(ReorderFilesRequest $request): JsonResponse
    {
        $model = app($request->get('fileable_type'))->findOrFail($request->get('fileable_id'));

        $this->fileableService->reorderFiles(
            $model,
            $request->get('role'),
            $request->get('file_ids')
        );

        return response()->json(['message' => 'Files reordered successfully']);
    }

    public function sync(Request $request): JsonResponse
    {
        $request->validate([
            'fileable_type' => ['required', 'string'],
            'fileable_id' => ['required', 'integer'],
            'role' => ['required', 'string'],
            'file_ids' => ['required', 'array'],
            'file_ids.*' => ['integer', 'exists:shelf_files,id'],
        ]);

        $model = app($request->get('fileable_type'))->findOrFail($request->get('fileable_id'));

        $this->fileableService->syncFiles(
            $model,
            $request->get('file_ids'),
            $request->get('role')
        );

        return response()->json(['message' => 'Files synced successfully']);
    }

    public function fileAttachments(int $fileId): JsonResponse
    {
        $attachments = $this->fileableService->getFileAttachments($fileId);
        return response()->json(['data' => $attachments]);
    }
}