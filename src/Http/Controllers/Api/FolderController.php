<?php

namespace Hetbo\Shelf\Http\Controllers\Api;

use Hetbo\Shelf\Http\Requests\StoreFolderRequest;
use Hetbo\Shelf\Http\Requests\UpdateFolderRequest;
use Hetbo\Shelf\Rules\NotSelfParent;
use Hetbo\Shelf\Services\FolderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class FolderController extends Controller
{
    public function __construct(
        private FolderService $folderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        if ($request->has('tree')) {
            $folders = $this->folderService->getFolderTree(auth()->id());
        } elseif ($request->has('parent_id')) {
            $folders = $this->folderService->getChildren($request->get('parent_id'));
        } else {
            $folders = $this->folderService->getRootFolders(auth()->id());
        }

        return response()->json(['data' => $folders]);
    }

    public function store(StoreFolderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->id();

        $folder = $this->folderService->create($data);
        return response()->json(['data' => $folder], 201);
    }

    public function show(int $id): JsonResponse
    {
        $folder = $this->folderService->findOrFail($id);
        return response()->json(['data' => $folder]);
    }

    public function update(UpdateFolderRequest $request, int $id): JsonResponse
    {
        $folder = $this->folderService->update($id, $request->validated());
        return response()->json(['data' => $folder]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->folderService->delete($id);
        return response()->json(['message' => 'Folder deleted successfully']);
    }

    public function move(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:shelf_folders,id', new NotSelfParent($id)],
        ]);

        $folder = $this->folderService->move($id, $request->get('parent_id'));
        return response()->json(['data' => $folder]);
    }

    public function path(int $id): JsonResponse
    {
        $path = $this->folderService->getFolderPath($id);
        return response()->json(['data' => $path]);
    }

    public function children(int $id): JsonResponse
    {
        $children = $this->folderService->getChildren($id);
        return response()->json(['data' => $children]);
    }
}