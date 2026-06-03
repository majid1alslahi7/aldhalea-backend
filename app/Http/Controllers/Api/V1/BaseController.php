<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Pagination\LengthAwarePaginator;

class BaseController extends Controller
{
    /**
     * نجاح مع بيانات
     */
    protected function successResponse($data, string $message = null, int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        return response()->json($response, $code);
    }

    /**
     * نجاح مع Resource
     */
    protected function resourceResponse(JsonResource $resource, string $message = null, int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $resource,
        ];

        return response()->json($response, $code);
    }

    /**
     * نجاح مع ResourceCollection
     */
    protected function collectionResponse(ResourceCollection $collection, string $message = null, int $code = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        return $collection->additional($response)->response()->setStatusCode($code);
    }

    /**
     * نجاح مع Pagination
     */
    protected function paginatedResponse(LengthAwarePaginator $paginator, string $message = null): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
                'has_more' => $paginator->hasMorePages(),
                'links' => [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
            ],
        ]);
    }

    /**
     * خطأ
     */
    protected function errorResponse(string $message, int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * رسالة إنشاء ناجح
     */
    protected function createdResponse($data, string $message = 'تم الإنشاء بنجاح'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * رسالة تحديث ناجح
     */
    protected function updatedResponse($data, string $message = 'تم التحديث بنجاح'): JsonResponse
    {
        return $this->successResponse($data, $message);
    }

    /**
     * رسالة حذف ناجح
     */
    protected function deletedResponse(string $message = 'تم الحذف بنجاح'): JsonResponse
    {
        return $this->successResponse(null, $message);
    }

    /**
     * غير موجود
     */
    protected function notFoundResponse(string $message = 'غير موجود'): JsonResponse
    {
        return $this->errorResponse($message, 404);
    }

    /**
     * غير مصرح
     */
    protected function unauthorizedResponse(string $message = 'غير مصرح'): JsonResponse
    {
        return $this->errorResponse($message, 403);
    }

    /**
     * خطأ تحقق
     */
    protected function validationErrorResponse($errors, string $message = 'خطأ في البيانات المدخلة'): JsonResponse
    {
        return $this->errorResponse($message, 422, $errors);
    }
}
