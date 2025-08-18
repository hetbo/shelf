<?php

namespace Hetbo\Shelf\Http\Controllers\Api;

use Hetbo\Shelf\Http\Requests\BulkFileMetadataRequest;
use Hetbo\Shelf\Http\Requests\SetFileMetadataRequest;
use Hetbo\Shelf\Services\FileMetadataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FileMetadataController extends Controller
{
    public function __construct(
        private FileMetadataService $metadataService
    ) {}

    public function index(int $fileId): JsonResponse
    {
        $metadata = $this->metadataService->getAllMetadata($fileId);
        return response()->json(['data' => $metadata]);
    }

    public function store(SetFileMetadataRequest $request, int $fileId): JsonResponse
    {
        $metadata = $this->metadataService->setMetadata(
            $fileId,
            $request->get('key'),
            $request->get('value')
        );

        return response()->json(['data' => $metadata], 201);
    }

    public function show(int $fileId, string $key): JsonResponse
    {
        $value = $this->metadataService->getMetadata($fileId, $key);

        if ($value === null) {
            return response()->json(['message' => 'Metadata key not found'], 404);
        }

        return response()->json(['data' => ['key' => $key, 'value' => $value]]);
    }

    public function update(SetFileMetadataRequest $request, int $fileId, string $key): JsonResponse
    {
        $this->metadataService->updateMetadata($fileId, $key, $request->get('value'));
        return response()->json(['message' => 'Metadata updated successfully']);
    }

    public function destroy(int $fileId, string $key): JsonResponse
    {
        $this->metadataService->deleteMetadata($fileId, $key);
        return response()->json(['message' => 'Metadata deleted successfully']);
    }

    public function bulkStore(BulkFileMetadataRequest $request, int $fileId): JsonResponse
    {
        $metadata = $this->metadataService->setMultiple($fileId, $request->get('metadata'));
        return response()->json(['data' => $metadata], 201);
    }

    public function bulkShow(Request $request, int $file): JsonResponse
    {
        $request->validate([
            'keys' => ['required', 'array'],
            'keys.*' => ['string'],
        ]);
        $metadata = $this->metadataService->getMultiple($file, $request->get('keys'));
        return response()->json(['data' => $metadata]);
    }

    public function destroyAll(int $fileId): JsonResponse
    {
        $this->metadataService->deleteAllMetadata($fileId);
        return response()->json(['message' => 'All metadata deleted successfully']);
    }
}
