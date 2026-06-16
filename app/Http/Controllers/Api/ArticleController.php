<?php

namespace App\Http\Controllers\Api;

use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Facades\URLHelper;

class ArticleController extends ApiController
{
    public function Show($id): \Illuminate\Http\JsonResponse
    {
        $article = Article::findOrFail($id);
        $article->date = $article->created_at->translatedFormat('j F Y');

        $article->image = URLHelper::transform($article->image);

        DB::table('article_view_log')->insertOrIgnore([
            'user_id' => $this->session->user_id,
            'time' => Carbon::now(),
            'article_id' => $article->id,
        ]);

        return response()->json($article, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function Promotions(): \Illuminate\Http\JsonResponse
    {
        $now = Carbon::now();
        $lastVisit = $this->counters->getPromotionsLastVisit();

        /** @var Article[] $articles */
        $articles = Article::whereIn('type_id', [2, 3, 4])
            ->where(function (Builder $q) use ($now) {
                $q->where(function (Builder $q) use ($now) {
                    $q->whereNotNull('start_at')->whereNotNull('end_at')->where('start_at', '<', $now)->where('end_at', '>', $now);
                })
                ->orWhere(function (Builder $q) use ($now) {
                    $q->whereNotNull('start_at')->whereNull('end_at')->where('start_at', '<', $now);
                })
                ->orWhere(function (Builder $q) use ($now) {
                    $q->whereNull('start_at')->whereNull('end_at');
                });
            })
            ->where('published', 1)->orderBy('created_at', 'DESC')->get();

        foreach ($articles as $article) {
            $article->duration = '';
            if ($article->start_at && $article->end_at && $article->start_at->isSameDay($article->end_at)) {
                $article->duration = 'Только ' . $article->start_at->translatedFormat('j F Y');
            }
            if (!$article->start_at && !$article->end_at) {
                $article->duration = 'Акция действует до её отмены';
            }
            if ($article->start_at && !$article->end_at) {
                $article->duration = 'с ' . $article->start_at->translatedFormat('j F Y') . ' до ее отмены';
            }

            if ($article->start_at && $article->end_at) {
                $article->duration = 'с ' . $article->start_at->translatedFormat('j F Y') . ' до ' . $article->end_at->translatedFormat('j F Y');
            }

            $article->image = URLHelper::transform($article->image);

            if ($lastVisit) {
                if ($article->created_at > $lastVisit) {
                    $article->new = true;
                }
            }
        }

        $this->counters->resetForPromotion();

        return response()->json($articles, 200, [], JSON_UNESCAPED_UNICODE);
    }

}
