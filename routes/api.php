<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\NewsController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\InvestigationController;
use App\Http\Controllers\Api\V1\InterviewController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\PollController;
use App\Http\Controllers\Api\V1\TagController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\BookmarkController;
use App\Http\Controllers\Api\V1\LikeController;
use App\Http\Controllers\Api\V1\ShareController;
use App\Http\Controllers\Api\V1\CitizenReportController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\NewsletterController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\CorrectionReportController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\SettingController;
use App\Http\Controllers\Api\V1\BreakingNewsController;
use App\Http\Controllers\Api\V1\MediaController;

/*
|--------------------------------------------------------------------------
| API Routes - الضالع أونلاين v1
|--------------------------------------------------------------------------
*/

// ==================== المصادقة ====================
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/password', [AuthController::class, 'updatePassword']);
    });
});

// ==================== المحتوى العام ====================
Route::prefix('v1')->group(function () {

    // --- الأخبار ---
    Route::prefix('news')->group(function () {
        Route::get('/', [NewsController::class, 'index']);
        Route::get('/breaking', [NewsController::class, 'breaking']);
        Route::get('/featured', [NewsController::class, 'featured']);
        Route::get('/trending', [NewsController::class, 'trending']);
        Route::get('/editors-picks', [NewsController::class, 'editorsPicks']);
        Route::get('/local', [NewsController::class, 'local']);
        Route::get('/popular', [NewsController::class, 'popular']);
        Route::get('/latest', [NewsController::class, 'latest']);
        Route::get('/by-date/{date}', [NewsController::class, 'byDate']);
        Route::get('/{slug}', [NewsController::class, 'show']);
        Route::get('/{id}/related', [NewsController::class, 'related']);
        Route::get('/{id}/next-previous', [NewsController::class, 'nextPrevious']);
    });

    // --- التصنيفات ---
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/menu', [CategoryController::class, 'menu']);
        Route::get('/tree', [CategoryController::class, 'tree']);
        Route::get('/popular', [CategoryController::class, 'popular']);
        Route::get('/{slug}', [CategoryController::class, 'show']);
        Route::get('/{slug}/news', [CategoryController::class, 'news']);
        Route::get('/{slug}/subcategories', [CategoryController::class, 'subcategories']);
    });

    // --- المقالات ---
    Route::prefix('articles')->group(function () {
        Route::get('/', [ArticleController::class, 'index']);
        Route::get('/opinions', [ArticleController::class, 'opinions']);
        Route::get('/analysis', [ArticleController::class, 'analysis']);
        Route::get('/columns', [ArticleController::class, 'columns']);
        Route::get('/featured', [ArticleController::class, 'featured']);
        Route::get('/popular', [ArticleController::class, 'popular']);
        Route::get('/latest', [ArticleController::class, 'latest']);
        Route::get('/writer/{writerId}', [ArticleController::class, 'byWriter']);
        Route::get('/{slug}', [ArticleController::class, 'show']);
        Route::get('/{id}/related', [ArticleController::class, 'related']);
    });

    // --- التقارير ---
    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportController::class, 'index']);
        Route::get('/photo', [ReportController::class, 'photoReports']);
        Route::get('/video', [ReportController::class, 'videoReports']);
        Route::get('/featured', [ReportController::class, 'featured']);
        Route::get('/latest', [ReportController::class, 'latest']);
        Route::get('/{slug}', [ReportController::class, 'show']);
    });

    // --- التحقيقات ---
    Route::prefix('investigations')->group(function () {
        Route::get('/', [InvestigationController::class, 'index']);
        Route::get('/featured', [InvestigationController::class, 'featured']);
        Route::get('/urgent', [InvestigationController::class, 'urgent']);
        Route::get('/latest', [InvestigationController::class, 'latest']);
        Route::get('/{slug}', [InvestigationController::class, 'show']);
    });

    // --- المقابلات ---
    Route::prefix('interviews')->group(function () {
        Route::get('/', [InterviewController::class, 'index']);
        Route::get('/video', [InterviewController::class, 'video']);
        Route::get('/podcast', [InterviewController::class, 'podcast']);
        Route::get('/featured', [InterviewController::class, 'featured']);
        Route::get('/latest', [InterviewController::class, 'latest']);
        Route::get('/{slug}', [InterviewController::class, 'show']);
    });

    // --- الوسوم ---
    Route::prefix('tags')->group(function () {
        Route::get('/', [TagController::class, 'index']);
        Route::get('/trending', [TagController::class, 'trending']);
        Route::get('/popular', [TagController::class, 'popular']);
        Route::get('/{slug}', [TagController::class, 'show']);
        Route::get('/{slug}/news', [TagController::class, 'news']);
        Route::get('/{slug}/articles', [TagController::class, 'articles']);
    });

    // --- التعليقات ---
    Route::get('/comments/{type}/{id}', [CommentController::class, 'index']);

    // --- استطلاعات الرأي ---
    Route::prefix('polls')->group(function () {
        Route::get('/', [PollController::class, 'index']);
        Route::get('/active', [PollController::class, 'active']);
        Route::get('/featured', [PollController::class, 'featured']);
        Route::get('/{id}', [PollController::class, 'show']);
        Route::get('/{id}/results', [PollController::class, 'results']);
        Route::post('/{id}/vote', [PollController::class, 'vote'])->middleware('throttle:10,1');
    });

    // --- البحث ---
    Route::prefix('search')->group(function () {
        Route::get('/', [SearchController::class, 'index']);
        Route::get('/suggestions', [SearchController::class, 'suggestions']);
        Route::get('/trending', [SearchController::class, 'trendingSearches']);
    });

    // --- الكتّاب ---
    Route::prefix('writers')->group(function () {
        Route::get('/', [UserController::class, 'writers']);
        Route::get('/featured', [UserController::class, 'featuredWriters']);
        Route::get('/{id}', [UserController::class, 'writerProfile']);
        Route::get('/{id}/articles', [UserController::class, 'writerArticles']);
    });

    // --- إعدادات الموقع ---
    Route::get('/settings', [SettingController::class, 'public']);
    Route::get('/breaking-news-bar', [BreakingNewsController::class, 'active']);

    // --- النشرة البريدية ---
    Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe']);
    Route::post('/newsletter/unsubscribe', [NewsletterController::class, 'unsubscribe']);

    // --- التواصل ---
    Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1');

    // --- الثقة والمساءلة التحريرية ---
    Route::post('/corrections/report', [CorrectionReportController::class, 'store'])->middleware('throttle:5,1');
    Route::post('/shares', [ShareController::class, 'store'])->middleware('throttle:30,1');
});

// ==================== مسارات المستخدم (تسجيل دخول مطلوب) ====================
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {

    // --- التعليقات ---
    Route::post('/comments/{type}/{id}', [CommentController::class, 'store']);
    Route::put('/comments/{id}', [CommentController::class, 'update']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

    // --- الإعجابات ---
    Route::post('/likes/toggle', [LikeController::class, 'toggle']);
    Route::get('/likes/my', [LikeController::class, 'myLikes']);

    // --- الإشارات المرجعية ---
    Route::prefix('bookmarks')->group(function () {
        Route::get('/', [BookmarkController::class, 'index']);
        Route::post('/toggle', [BookmarkController::class, 'toggle']);
        Route::delete('/{id}', [BookmarkController::class, 'destroy']);
        Route::get('/check/{type}/{id}', [BookmarkController::class, 'check']);
    });

    // --- تقارير المواطن ---
    Route::prefix('citizen-reports')->group(function () {
        Route::post('/', [CitizenReportController::class, 'store']);
        Route::get('/my', [CitizenReportController::class, 'myReports']);
        Route::get('/{id}', [CitizenReportController::class, 'show']);
    });

    // --- الإشعارات ---
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/read-all', [NotificationController::class, 'markAllAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });

    // --- تفضيلات المستخدم ---
    Route::get('/preferences', [UserController::class, 'preferences']);
    Route::put('/preferences', [UserController::class, 'updatePreferences']);
});

// ==================== لوحة التحكم (كتّاب/محررين/مشرفين) ====================
Route::middleware(['auth:sanctum'])->prefix('v1/dashboard')->group(function () {

    Route::get('/stats', [DashboardController::class, 'stats']);
    Route::get('/analytics', [DashboardController::class, 'analytics']);
    Route::get('/recent-activity', [DashboardController::class, 'recentActivity']);

    // إدارة الأخبار
    Route::prefix('news')->group(function () {
        Route::post('/', [NewsController::class, 'store']);
        Route::put('/{id}', [NewsController::class, 'update']);
        Route::delete('/{id}', [NewsController::class, 'destroy']);
        Route::put('/{id}/status', [NewsController::class, 'updateStatus']);
        Route::put('/{id}/priority', [NewsController::class, 'updatePriority']);
    });

    // إدارة المقالات
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::put('/articles/{id}', [ArticleController::class, 'update']);
    Route::delete('/articles/{id}', [ArticleController::class, 'destroy']);

    // إدارة التقارير
    Route::post('/reports', [ReportController::class, 'store']);
    Route::put('/reports/{id}', [ReportController::class, 'update']);
    Route::delete('/reports/{id}', [ReportController::class, 'destroy']);

    // إدارة التحقيقات
    Route::post('/investigations', [InvestigationController::class, 'store']);
    Route::put('/investigations/{id}', [InvestigationController::class, 'update']);
    Route::delete('/investigations/{id}', [InvestigationController::class, 'destroy']);

    // إدارة المقابلات
    Route::post('/interviews', [InterviewController::class, 'store']);
    Route::put('/interviews/{id}', [InterviewController::class, 'update']);
    Route::delete('/interviews/{id}', [InterviewController::class, 'destroy']);

    // إدارة الوسائط
    Route::prefix('media')->group(function () {
        Route::get('/', [MediaController::class, 'index']);
        Route::post('/upload', [MediaController::class, 'upload']);
        Route::delete('/{id}', [MediaController::class, 'destroy']);
    });

    // إدارة التعليقات
    Route::prefix('comments')->group(function () {
        Route::get('/pending', [CommentController::class, 'pending']);
        Route::put('/{id}/approve', [CommentController::class, 'approve']);
        Route::put('/{id}/reject', [CommentController::class, 'reject']);
    });

    // إدارة استطلاعات الرأي
    Route::post('/polls', [PollController::class, 'store']);
    Route::put('/polls/{id}', [PollController::class, 'update']);
    Route::delete('/polls/{id}', [PollController::class, 'destroy']);

    // إدارة تقارير المواطن
    Route::prefix('citizen-reports')->group(function () {
        Route::get('/all', [CitizenReportController::class, 'all']);
        Route::put('/{id}/review', [CitizenReportController::class, 'review']);
        Route::put('/{id}/approve', [CitizenReportController::class, 'approve']);
    });
});

// ==================== لوحة تحكم المشرف ====================
Route::middleware(['auth:sanctum'])->prefix('v1/admin')->group(function () {

    // إدارة المستخدمين
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::put('/{id}/role', [UserController::class, 'updateRole']);
        Route::put('/{id}/status', [UserController::class, 'toggleStatus']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });

    // إدارة التصنيفات
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{id}', [CategoryController::class, 'update']);
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

    // إدارة الوسوم
    Route::post('/tags', [TagController::class, 'store']);
    Route::put('/tags/{id}', [TagController::class, 'update']);
    Route::delete('/tags/{id}', [TagController::class, 'destroy']);

    // إدارة الأخبار العاجلة
    Route::prefix('breaking-news')->group(function () {
        Route::post('/', [BreakingNewsController::class, 'store']);
        Route::put('/{id}', [BreakingNewsController::class, 'update']);
        Route::delete('/{id}', [BreakingNewsController::class, 'destroy']);
    });

    // إعدادات الموقع
    Route::get('/settings', [SettingController::class, 'all']);
    Route::put('/settings', [SettingController::class, 'update']);

    // بلاغات التصحيح التحريري
    Route::get('/corrections/reports', [CorrectionReportController::class, 'index']);
    Route::put('/corrections/reports/{id}/status', [CorrectionReportController::class, 'updateStatus']);

    // الإحصائيات المتقدمة
    Route::get('/analytics/overview', [DashboardController::class, 'adminOverview']);
    Route::get('/analytics/traffic', [DashboardController::class, 'trafficStats']);
    Route::get('/analytics/content', [DashboardController::class, 'contentStats']);
    Route::get('/analytics/users', [DashboardController::class, 'userStats']);
});

// ==================== Sitemap & RSS ====================
Route::prefix('feeds')->group(function () {
    Route::get('/rss', [App\Http\Controllers\Api\V1\FeedController::class, 'rss']);
    Route::get('/sitemap.xml', [App\Http\Controllers\Api\V1\FeedController::class, 'sitemap']);
});
