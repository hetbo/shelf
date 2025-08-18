<?php

namespace Hetbo\Shelf\Http\Controllers\Api;

use Hetbo\Shelf\Http\Requests\BulkFileRequest;
use Hetbo\Shelf\Http\Requests\MoveFileRequest;
use Hetbo\Shelf\Http\Requests\StoreFileRequest;
use Hetbo\Shelf\Http\Requests\UpdateFileRequest;
use Hetbo\Shelf\Services\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FileController extends Controller
{
    public function __construct(
        private FileService $fileService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = array_filter([
            'user_id' => auth()->id(),
            'folder_id' => $request->get('folder_id'),
            'mime_type' => $request->get('mime_type'),
        ]);

        if ($request->has('search')) {
            $files = $this->fileService->search($request->get('search'), $filters);
            return response()->json(['data' => $files]);
        }

        $files = $this->fileService->paginate(
            $request->get('per_page', 15),
            $filters
        );

        return response()->json($files);
    }

    public function store(StoreFileRequest $request): JsonResponse
    {
        $file = $this->fileService->upload(
            $request->file('file'),
            $request->get('folder_id'),
            $request->get('disk')
        );

        return response()->json(['data' => $file], 201);
    }

    public function show(int $id): JsonResponse
    {
        $file = $this->fileService->findOrFail($id);
        return response()->json(['data' => $file]);
    }

    public function update(UpdateFileRequest $request, int $id): JsonResponse
    {
        $file = $this->fileService->update($id, $request->validated());
        return response()->json(['data' => $file]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->fileService->delete($id);
        return response()->json(['message' => 'File deleted successfully']);
    }

    public function download(int $id): JsonResponse
    {
        $url = $this->fileService->getDownloadUrl($id);
        return response()->json(['download_url' => $url]);
    }

    public function move(MoveFileRequest $request, int $id): JsonResponse
    {
        $file = $this->fileService->move($id, $request->get('folder_id'));
        return response()->json(['data' => $file]);
    }

    public function duplicate(int $id): JsonResponse
    {
        $file = $this->fileService->duplicate($id);
        return response()->json(['data' => $file], 201);
    }

    public function bulk(BulkFileRequest $request): JsonResponse
    {
        $action = $request->get('action');
        $fileIds = $request->get('file_ids');

        $result = match($action) {
            'delete' => [
                'deleted' => $this->fileService->bulkDelete($fileIds),
                'message' => 'Files deleted successfully'
            ],
            'move' => [
                'moved' => $this->fileService->bulkMove($fileIds, $request->get('folder_id')),
                'message' => 'Files moved successfully'
            ],
        };

        return response()->json($result);
    }

    public function contents(int $id): JsonResponse
    {
        $contents = $this->fileService->getFileContents($id);
        return response()->json(['contents' => $contents]);
    }
}