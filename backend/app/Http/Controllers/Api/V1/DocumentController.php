<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Filters\DocumentFilter;
use App\Http\Requests\Api\V1\StoreDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function __construct(private readonly DocumentService $service) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Document::class);

        $query = Document::visibleTo($request->user());

        $documents = (new DocumentFilter($request))->apply($query)
            ->orderByDesc('created_at')
            ->paginate(min((int) $request->integer('per_page', 15), 100));

        return DocumentResource::collection($documents);
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        $this->authorize('create', Document::class);

        $document = $this->service->store(
            $request->file('file'),
            $request->safe()->except(['file']),
            $request->user(),
        );

        return DocumentResource::make($document)->response()->setStatusCode(201);
    }

    public function show(Request $request, int $document): DocumentResource
    {
        $model = Document::visibleTo($request->user())->findOrFail($document);

        $this->authorize('view', $model);

        return DocumentResource::make($model);
    }

    public function download(Request $request, int $document): StreamedResponse
    {
        $model = Document::visibleTo($request->user())->findOrFail($document);

        $this->authorize('view', $model);

        return Storage::disk('local')->download($model->file_path, $model->original_name);
    }

    public function destroy(Request $request, int $document): JsonResponse
    {
        $model = Document::visibleTo($request->user())->findOrFail($document);

        $this->authorize('delete', $model);

        $this->service->delete($model);

        return response()->json(null, 204);
    }
}
