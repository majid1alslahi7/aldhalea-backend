<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Article;
use App\Models\Category;
use App\Models\Interview;
use App\Models\Investigation;
use App\Models\News;
use App\Models\Poll;
use App\Models\Report;
use App\Models\Tag;
use App\Models\User;
use Carbon\CarbonInterface;

class FeedController extends BaseController
{
    public function rss()
    {
        $news = News::published()->latest()->limit(20)->get();
        return response()->view('feeds.rss', ['news' => $news])->header('Content-Type', 'text/xml');
    }

    public function sitemap()
    {
        $urls = array_merge(
            $this->staticUrls(),
            $this->categoryUrls(),
            $this->contentUrls(News::published()->where(function ($query) {
                $query->whereNull('no_index')->orWhere('no_index', false);
            })->latest('published_at')->limit(50000)->get(), 'news', 'daily', '0.9'),
            $this->contentUrls(Article::published()->latest('published_at')->limit(20000)->get(), 'articles', 'weekly', '0.8'),
            $this->contentUrls(Report::published()->latest('published_at')->limit(10000)->get(), 'reports', 'weekly', '0.8'),
            $this->contentUrls(Investigation::published()->latest('published_at')->limit(10000)->get(), 'investigations', 'weekly', '0.85'),
            $this->contentUrls(Interview::published()->latest('published_at')->limit(10000)->get(), 'interviews', 'weekly', '0.75'),
            $this->tagUrls(),
            $this->writerUrls(),
            $this->pollUrls(),
        );

        $xml = $this->renderSitemap($urls);

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots()
    {
        $content = implode("\n", [
            'User-agent: *',
            'Allow: /',
            '',
            'Sitemap: ' . $this->frontendUrl('/sitemap.xml'),
            'Sitemap: ' . rtrim(config('app.url'), '/') . '/sitemap.xml',
            '',
        ]);

        return response($content, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function llms()
    {
        $content = implode("\n", [
            '# الضالع أونلاين',
            '',
            'منصة إخبارية يمنية تغطي أخبار الضالع واليمن والتقارير والتحقيقات والمقالات.',
            '',
            '## Canonical',
            '- ' . $this->frontendUrl('/'),
            '- ' . $this->frontendUrl('/sitemap.xml'),
            '- ' . rtrim(config('app.url'), '/') . '/sitemap.xml',
            '',
            '## Content',
            '- News: /news/{slug}',
            '- Articles: /articles/{slug}',
            '- Reports: /reports/{slug}',
            '- Investigations: /investigations/{slug}',
            '- Interviews: /interviews/{slug}',
            '- Archives: /news, /articles, /reports, /investigations, /interviews, /archive',
            '- Discovery: /tags, /writers, /polls, /search',
            '- Policies: /privacy, /terms, /editorial-policy, /corrections-policy, /team, /ownership, /advertise, /ethics',
            '',
            '## Agent guidance',
            '- Prefer canonical URLs from sitemap and page meta tags.',
            '- Treat JSON-LD as the structured source for title, author, publisher, and dates.',
            '- The public frontend domain is environment-driven through FRONTEND_URL or APP_FRONTEND_URL.',
            '',
        ]);

        return response($content, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    private function staticUrls(): array
    {
        $now = now()->toAtomString();

        return [
            $this->urlEntry('/', $now, 'hourly', '1.0'),
            $this->urlEntry('/about', $now, 'monthly', '0.5'),
            $this->urlEntry('/contact', $now, 'monthly', '0.5'),
            $this->urlEntry('/search', $now, 'weekly', '0.4'),
            $this->urlEntry('/news', $now, 'hourly', '0.9'),
            $this->urlEntry('/articles', $now, 'daily', '0.8'),
            $this->urlEntry('/reports', $now, 'daily', '0.75'),
            $this->urlEntry('/investigations', $now, 'daily', '0.8'),
            $this->urlEntry('/interviews', $now, 'weekly', '0.7'),
            $this->urlEntry('/popular', $now, 'hourly', '0.7'),
            $this->urlEntry('/editors-picks', $now, 'daily', '0.7'),
            $this->urlEntry('/breaking', $now, 'hourly', '0.7'),
            $this->urlEntry('/archive', $now, 'daily', '0.6'),
            $this->urlEntry('/tags', $now, 'weekly', '0.6'),
            $this->urlEntry('/writers', $now, 'weekly', '0.6'),
            $this->urlEntry('/polls', $now, 'weekly', '0.5'),
            $this->urlEntry('/newsletter', $now, 'monthly', '0.4'),
            $this->urlEntry('/privacy', $now, 'monthly', '0.3'),
            $this->urlEntry('/terms', $now, 'monthly', '0.3'),
            $this->urlEntry('/editorial-policy', $now, 'monthly', '0.3'),
            $this->urlEntry('/corrections-policy', $now, 'monthly', '0.3'),
            $this->urlEntry('/team', $now, 'monthly', '0.35'),
            $this->urlEntry('/ownership', $now, 'monthly', '0.3'),
            $this->urlEntry('/advertise', $now, 'monthly', '0.3'),
            $this->urlEntry('/ethics', $now, 'monthly', '0.3'),
        ];
    }

    private function categoryUrls(): array
    {
        return Category::active()
            ->ordered()
            ->get()
            ->map(fn (Category $category) => $this->urlEntry(
                '/category/' . $this->localizedSlug($category),
                $this->lastModified($category),
                'daily',
                '0.7'
            ))
            ->filter(fn (array $entry) => $entry['loc'] !== $this->frontendUrl('/category'))
            ->values()
            ->all();
    }

    private function tagUrls(): array
    {
        return Tag::active()
            ->withContent()
            ->get()
            ->map(fn (Tag $tag) => $this->urlEntry(
                '/tags/' . $this->localizedSlug($tag),
                $this->lastModified($tag),
                'daily',
                '0.6'
            ))
            ->filter(fn (array $entry) => $entry['loc'] !== $this->frontendUrl('/tags'))
            ->values()
            ->all();
    }

    private function writerUrls(): array
    {
        return User::writers()
            ->active()
            ->get()
            ->map(fn (User $writer) => $this->urlEntry(
                '/writers/' . $writer->id,
                $this->lastModified($writer),
                'weekly',
                '0.55'
            ))
            ->values()
            ->all();
    }

    private function pollUrls(): array
    {
        return Poll::latest()
            ->limit(1000)
            ->get()
            ->map(fn (Poll $poll) => $this->urlEntry(
                '/polls/' . $poll->id,
                $this->lastModified($poll),
                'weekly',
                '0.45'
            ))
            ->values()
            ->all();
    }

    private function contentUrls($items, string $prefix, string $changefreq, string $priority): array
    {
        return $items
            ->map(fn ($item) => $this->urlEntry(
                '/' . $prefix . '/' . $this->localizedSlug($item),
                $this->lastModified($item),
                $changefreq,
                $priority
            ))
            ->filter(fn (array $entry) => $entry['loc'] !== $this->frontendUrl('/' . $prefix))
            ->values()
            ->all();
    }

    private function urlEntry(string $path, string $lastmod, string $changefreq, string $priority): array
    {
        return [
            'loc' => $this->frontendUrl($path),
            'lastmod' => $lastmod,
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }

    private function renderSitemap(array $urls): string
    {
        $items = collect($urls)
            ->unique('loc')
            ->map(function (array $url) {
                return implode("\n", [
                    '  <url>',
                    '    <loc>' . $this->xml($url['loc']) . '</loc>',
                    '    <lastmod>' . $this->xml($url['lastmod']) . '</lastmod>',
                    '    <changefreq>' . $this->xml($url['changefreq']) . '</changefreq>',
                    '    <priority>' . $this->xml($url['priority']) . '</priority>',
                    '  </url>',
                ]);
            })
            ->implode("\n");

        return implode("\n", [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">',
            $items,
            '</urlset>',
        ]);
    }

    private function frontendUrl(string $path): string
    {
        $base = rtrim(env('FRONTEND_URL') ?: env('APP_FRONTEND_URL') ?: env('SITE_URL') ?: $this->derivedFrontendUrl(), '/');
        $trimmed = trim($path, '/');

        if ($trimmed === '') {
            return $base . '/';
        }

        $encoded = collect(explode('/', $trimmed))
            ->map(fn (string $segment) => rawurlencode($segment))
            ->implode('/');

        return $base . '/' . $encoded;
    }

    private function derivedFrontendUrl(): string
    {
        $requestBase = request()->getSchemeAndHttpHost();

        if (str_contains($requestBase, 'backend')) {
            return str_replace('backend', 'frontend', $requestBase);
        }

        return config('app.url');
    }

    private function localizedSlug($model): string
    {
        $slug = method_exists($model, 'getTranslation')
            ? ($model->getTranslation('slug', 'ar', false) ?: $model->getTranslation('slug', 'en', false))
            : $model->slug;

        if (is_array($slug)) {
            $slug = $slug['ar'] ?? $slug['en'] ?? reset($slug);
        }

        return (string) $slug;
    }

    private function lastModified($model): string
    {
        $date = $model->updated_at ?? $model->published_at ?? now();

        if ($date instanceof CarbonInterface) {
            return $date->toAtomString();
        }

        return (string) $date;
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }
}
