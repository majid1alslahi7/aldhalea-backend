<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\News;
use App\Http\Requests\Api\V1\NewsRequest;
use App\Http\Resources\NewsResource;
use App\Http\Resources\NewsCollection;
use App\Services\NewsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class NewsController extends BaseController
{
    protected $newsService;

    public function __construct(NewsService $newsService)
    {
        $this->newsService = $newsService;
    }

    /**
     * عرض كل الأخبار مع فلترة متقدمة
     */
    public function index(Request $request)
    {
        $query = News::with(['category', 'writer', 'tags']);

        // الفلترة
        $query = $this->applyFilters($query, $request);

        // الترتيب
        $orderBy = $request->get('order_by', 'published_at');
        $direction = $request->get('direction', 'desc');
        $allowedOrder = ['published_at', 'views_count', 'created_at', 'title'];
        
        if (in_array($orderBy, $allowedOrder)) {
            $query->orderBy($orderBy, $direction === 'asc' ? 'asc' : 'desc');
        }

        // التقسيم لصفحات
        $perPage = min((int) $request->get('per_page', 15), 50);
        $news = $query->paginate($perPage);

        return $this->paginatedResponse($news, 'تم جلب الأخبار بنجاح');
    }

    /**
     * عرض خبر واحد مع التفاصيل
     */
    public function show($slug, Request $request)
    {
        $news = News::where('slug->ar', $slug)
                    ->orWhere('slug->en', $slug)
                    ->with([
                        'category',
                        'subCategory',
                        'writer',
                        'editor',
                        'tags',
                        'approvedComments' => function($q) {
                            $q->with(['user', 'replies.user'])->latest()->limit(20);
                        },
                    ])
                    ->first();

        if (!$news) {
            return $this->notFoundResponse('الخبر غير موجود');
        }

        // زيادة المشاهدات
        $this->newsService->incrementViews($news, $request);

        // جلب بيانات المستخدم إذا مسجل
        $userData = null;
        if ($request->user()) {
            $userData = [
                'is_bookmarked' => $news->bookmarks()->where('user_id', $request->user()->id)->exists(),
                'is_liked' => $news->likes()->where('user_id', $request->user()->id)->exists(),
                'like_type' => $news->likes()->where('user_id', $request->user()->id)->value('type'),
            ];
        }

        return (new NewsResource($news))
            ->additional([
                'success' => true,
                'user_data' => $userData,
                'related_news' => NewsResource::collection($news->getRelatedNews(4)),
                'next_news' => $news->getNextNews() ? new NewsResource($news->getNextNews()) : null,
                'previous_news' => $news->getPreviousNews() ? new NewsResource($news->getPreviousNews()) : null,
            ]);
    }

    /**
     * الأخبار العاجلة
     */
    public function breaking()
    {
        $cacheKey = 'breaking_news';
        $breakingNews = Cache::remember($cacheKey, 60, function () {
            return News::breaking()
                       ->with('category')
                       ->latest('published_at')
                       ->limit(10)
                       ->get();
        });

        return $this->successResponse(NewsResource::collection($breakingNews), 'الأخبار العاجلة');
    }

    /**
     * الأخبار المميزة
     */
    public function featured()
    {
        $featured = Cache::remember('featured_news', 300, function () {
            return News::published()
                       ->featured()
                       ->with(['category', 'writer'])
                       ->latest('published_at')
                       ->limit(5)
                       ->get();
        });

        return $this->successResponse(NewsResource::collection($featured));
    }

    /**
     * الأخبار الرائجة
     */
    public function trending(Request $request)
    {
        $days = $request->get('days', 7);
        $trending = News::popular($days)
                        ->with('category')
                        ->limit(10)
                        ->get();

        return $this->successResponse(NewsResource::collection($trending));
    }

    /**
     * اختيارات المحرر
     */
    public function editorsPicks()
    {
        $picks = Cache::remember('editors_picks_news', 600, function () {
            return News::published()
                       ->editorsPick()
                       ->with(['category', 'writer'])
                       ->latest('published_at')
                       ->limit(6)
                       ->get();
        });

        return $this->successResponse(NewsResource::collection($picks));
    }

    /**
     * الأخبار المحلية
     */
    public function local(Request $request)
    {
        $news = News::local()
                    ->published()
                    ->with('category')
                    ->latest('published_at')
                    ->paginate(15);

        return $this->paginatedResponse($news, 'أخبار الضالع');
    }

    /**
     * الأخبار الأكثر شعبية
     */
    public function popular(Request $request)
    {
        $period = $request->get('period', 'week'); // day, week, month, all
        $days = $period === 'day' ? 1 : ($period === 'month' ? 30 : ($period === 'all' ? 365 : 7));

        $popular = News::popular($days)
                       ->with('category')
                       ->limit(20)
                       ->get();

        return $this->successResponse(NewsResource::collection($popular));
    }

    /**
     * أحدث الأخبار
     */
    public function latest(Request $request)
    {
        $news = News::recent()
                    ->with(['category', 'tags'])
                    ->paginate(20);

        return $this->paginatedResponse($news);
    }

    /**
     * أخبار حسب التاريخ
     */
    public function byDate($date, Request $request)
    {
        $news = News::published()
                    ->whereDate('published_at', $date)
                    ->with('category')
                    ->latest('published_at')
                    ->paginate(20);

        return $this->paginatedResponse($news, "أخبار تاريخ {$date}");
    }

    /**
     * أخبار ذات صلة
     */
    public function related($id)
    {
        $news = News::find($id);
        if (!$news) {
            return $this->notFoundResponse();
        }

        $related = $news->getRelatedNews(6);
        return $this->successResponse(NewsResource::collection($related));
    }

    /**
     * الخبر السابق والتالي
     */
    public function nextPrevious($id)
    {
        $news = News::find($id);
        if (!$news) {
            return $this->notFoundResponse();
        }

        return $this->successResponse([
            'next' => $news->getNextNews() ? new NewsResource($news->getNextNews()) : null,
            'previous' => $news->getPreviousNews() ? new NewsResource($news->getPreviousNews()) : null,
        ]);
    }

    /**
     * إنشاء خبر جديد
     */
    public function store(NewsRequest $request)
    {
        $news = $this->newsService->create($request->validated(), $request->user());

        // مسح الكاش
        Cache::tags(['news', 'categories'])->flush();

        return $this->createdResponse(new NewsResource($news), 'تم نشر الخبر بنجاح');
    }

    /**
     * تحديث خبر
     */
    public function update($id, NewsRequest $request)
    {
        $news = News::find($id);
        if (!$news) {
            return $this->notFoundResponse();
        }

        $this->authorize('update', $news);

        $news = $this->newsService->update($news, $request->validated());

        Cache::tags(['news'])->flush();

        return $this->updatedResponse(new NewsResource($news), 'تم تحديث الخبر بنجاح');
    }

    /**
     * حذف خبر
     */
    public function destroy($id)
    {
        $news = News::find($id);
        if (!$news) {
            return $this->notFoundResponse();
        }

        $this->authorize('delete', $news);
        $news->delete();

        Cache::tags(['news'])->flush();

        return $this->deletedResponse('تم حذف الخبر بنجاح');
    }

    /**
     * تحديث حالة الخبر
     */
    public function updateStatus($id, Request $request)
    {
        $request->validate(['status' => 'required|in:draft,pending,published,archived,breaking,rejected']);

        $news = News::find($id);
        if (!$news) {
            return $this->notFoundResponse();
        }

        $news->update([
            'status' => $request->status,
            'published_at' => $request->status === 'published' ? now() : $news->published_at,
        ]);

        Cache::tags(['news'])->flush();

        return $this->updatedResponse(new NewsResource($news), 'تم تحديث الحالة');
    }

    /**
     * تحديث أولوية الخبر
     */
    public function updatePriority($id, Request $request)
    {
        $request->validate(['priority' => 'required|in:normal,featured,breaking,editors_pick,trending']);

        $news = News::find($id);
        if (!$news) {
            return $this->notFoundResponse();
        }

        $news->update(['priority' => $request->priority]);

        return $this->updatedResponse(new NewsResource($news), 'تم تحديث الأولوية');
    }

    /**
     * رفع وسائط للخبر
     */
    public function uploadMedia($id, Request $request)
    {
        $news = News::find($id);
        if (!$news) {
            return $this->notFoundResponse();
        }

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,webp|max:10240',
            'collection' => 'required|in:main_images,gallery,thumbnails',
        ]);

        $media = $news->addMediaFromRequest('file')
                      ->toMediaCollection($request->collection);

        return $this->createdResponse([
            'id' => $media->id,
            'url' => $media->getUrl(),
            'name' => $media->file_name,
        ], 'تم رفع الوسيط بنجاح');
    }

    /**
     * حذف وسائط
     */
    public function deleteMedia($id, $mediaId)
    {
        $news = News::find($id);
        if (!$news) {
            return $this->notFoundResponse();
        }

        $media = $news->media()->find($mediaId);
        if ($media) {
            $media->delete();
        }

        return $this->deletedResponse('تم حذف الوسيط');
    }

    /**
     * تطبيق الفلاتر على الاستعلام
     */
    private function applyFilters($query, Request $request)
    {
        // الفلترة حسب الحالة
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            $query->published();
        }

        // الفلترة حسب التصنيف
        if ($request->has('category')) {
            $category = \App\Models\Category::where('slug->ar', $request->category)
                                            ->orWhere('slug->en', $request->category)
                                            ->first();
            if ($category) {
                $query->byCategory($category->id);
            }
        }

        // الفلترة حسب التصنيف الفرعي
        if ($request->has('sub_category')) {
            $query->where('sub_category_id', $request->sub_category);
        }

        // الفلترة حسب الوسم
        if ($request->has('tag')) {
            $tag = \App\Models\Tag::where('slug->ar', $request->tag)
                                  ->orWhere('slug->en', $request->tag)
                                  ->first();
            if ($tag) {
                $query->byTag($tag->id);
            }
        }

        // الفلترة حسب الكاتب
        if ($request->has('writer')) {
            $query->byWriter($request->writer);
        }

        // الفلترة حسب المحرر
        if ($request->has('editor')) {
            $query->where('editor_id', $request->editor);
        }

        // الفلترة حسب النوع
        if ($request->has('format')) {
            $query->byFormat($request->format);
        }

        // الفلترة حسب الأولوية
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // الفلترة حسب الموقع
        if ($request->has('location')) {
            $query->where('location', 'LIKE', "%{$request->location}%");
        }

        if ($request->has('city')) {
            $query->where('city', $request->city);
        }

        if ($request->has('district')) {
            $query->where('district', $request->district);
        }

        // الفلترة حسب المحتوى المحلي فقط
        if ($request->boolean('local_only')) {
            $query->local();
        }

        // الفلترة حسب المحتوى الممول
        if ($request->has('sponsored')) {
            $query->sponsored();
        }

        // الفلترة حسب نطاق التاريخ
        if ($request->has('date_from') && $request->has('date_to')) {
            $query->dateBetween($request->date_from, $request->date_to);
        } elseif ($request->has('date')) {
            $query->whereDate('published_at', $request->date);
        }

        // الفلترة حسب الأخبار العاجلة
        if ($request->boolean('breaking_only')) {
            $query->breaking();
        }

        // الفلترة حسب اختيارات المحرر
        if ($request->boolean('editors_pick_only')) {
            $query->editorsPick();
        }

        // البحث النصي
        if ($request->has('search')) {
            $query->search($request->search);
        }

        // فلترة المصدر
        if ($request->has('source')) {
            $query->where('source_name', 'LIKE', "%{$request->source}%");
        }

        return $query;
    }
}
