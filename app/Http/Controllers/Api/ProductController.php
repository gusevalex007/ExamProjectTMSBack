<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Получить список продуктов проекта
     * GET /api/projects/{project}/products
     */
    public function index(Project $project, Request $request)
    {
        // Получаем разрешённые типы для этого шаблона
        $allowedTypes = $project->getAllowedProductTypes();

        $query = $project->products()
            ->whereIn('type', $allowedTypes);

        // Фильтр по конкретному типу
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // // Фильтр по категории
        // if ($request->filled('category_id')) {
        //     $query->where('category_id', $request->category_id);
        // }

        // Только активные
        if ($request->filled('active')) {
            $query->active();
        }

        // Только избранные
        if ($request->filled('featured')) {
            $query->featured();
        }

        // // Загружаем связи
        // $query->with(['category', 'files']);

        // Сортировка
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $products = $query->get();

        return response()->json($products);
    }

    /**
     * Создать новый продукт
     * POST /api/projects/{project}/products
     */
    public function store(Request $request, Project $project)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'nullable|string|in:product,digital_product,course,lesson,portfolio_item,menu_item',
            'category_id' => 'nullable|exists:categories,id',
            'category' => 'nullable|string',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'old_price' => 'nullable|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'discount' => 'nullable|integer|min:0|max:100',
            'image_url' => 'nullable|url|max:500',
            'metadata' => 'nullable|array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'files' => 'nullable|array',
            'files.*' => 'file|max:102400', // 100MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        // Если тип не указан, используем дефолтный для шаблона
        if (empty($data['type'])) {
            $data['type'] = $project->getDefaultProductType();
        }

        // Создаём продукт
        $product = $project->products()->create($data);

        // Обработка файлов
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('products/' . $product->id, 'public');
                
                $product->files()->create([
                    'file_url' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_type' => $file->extension(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }

        // Перезагружаем с relations_пока отключаем
        // $product->load(['category', 'files']);

        return response()->json($product, 201);
    }

    /**
     * Получить один продукт
     * GET /api/products/{product}
     */
    public function show(Product $product)
    {
        $product->load(['category', 'files', 'children.child', 'parent.parent']);
        
        return response()->json($product);
    }

    /**
     * Обновить продукт
     * PATCH /api/products/{product}
     */
    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'sometimes|string|in:product,digital_product,course,lesson,portfolio_item,menu_item',
            // 'category_id' => 'sometimes|nullable|exists:categories,id',
            'category' => 'sometimes|nullable|string',
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'price' => 'sometimes|nullable|numeric|min:0',
            'old_price' => 'sometimes|nullable|numeric|min:0',
            'stock' => 'sometimes|nullable|integer|min:0',
            'discount' => 'sometimes|nullable|integer|min:0|max:100',
            'image_url' => 'sometimes|nullable|url|max:500',
            'metadata' => 'sometimes|nullable|array',
            'is_active' => 'sometimes|boolean',
            'is_featured' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $product->update($validator->validated());

        // $product->load(['category', 'files']);

        return response()->json($product);
    }

    /**
     * Удалить продукт
     * DELETE /api/products/{product}
     */
    public function destroy(Product $product)
    {
        // Удаление файлов из storage
        foreach ($product->files as $file) {
            if (Storage::disk('public')->exists($file->file_url)) {
                Storage::disk('public')->delete($file->file_url);
            }
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    /**
     * Загрузить файл для продукта
     * POST /api/products/{product}/files
     */
    public function uploadFile(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:102400',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $file = $request->file('file');
        $path = $file->store('products/' . $product->id, 'public');

        $productFile = $product->files()->create([
            'file_url' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $file->extension(),
            'file_size' => $file->getSize(),
        ]);

        return response()->json($productFile, 201);
    }

    /**
     * Удалить файл продукта
     * DELETE /api/product-files/{fileId}
     */
    public function deleteFile($fileId)
    {
        $file = \App\Models\ProductFile::findOrFail($fileId);

        if (Storage::disk('public')->exists($file->file_url)) {
            Storage::disk('public')->delete($file->file_url);
        }

        $file->delete();

        return response()->json(['message' => 'File deleted successfully']);
    }
}
