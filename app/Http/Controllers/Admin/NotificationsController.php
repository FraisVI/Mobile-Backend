<?php

namespace App\Http\Controllers\Admin;

use App\Facades\URLHelper;
use App\Jobs\CreateNotificationsForSegmentJob;
use App\Models\Article;
use App\Models\Segment;
use App\Models\Session;
use App\FirebaseNotificationService;
use App\Support\FirebaseLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationsController extends AdminController
{
    const TOKEN_LIMIT = 200;

    var array $labels = [
        'Конкурс' => 'warning',
        'Важно' => 'danger',
        'Промо' => 'primary',
        'Сервисное' => 'primary',
        'Опрос' => 'warning',
    ];

    public function __construct(private FirebaseNotificationService $firebaseService)
    {
        parent::__construct();
    }

    public function Show()
    {
        $title = 'Рекламные рассылки';
        $labels = $this->labels;

        $articles = Article::whereIn('type_id', [1, 2, 3, 4])
            ->where('published', 1)
            ->orderBy('updated_at', 'DESC')
            ->get();

        $segments = Segment::getElements();

        return view('admin.notifications', compact('title', 'labels', 'segments', 'articles'));
    }

    public function send(Request $request)
    {
        $segment_ids = array_values(array_map('intval', (array) $request->get('segment_id', [])));
        $title = $request->get('title');
        $message = $request->get('message');
        $article_id = (int) $request->get('article_id', 0);
        $label = $request->get('label');
        $top_priority = (bool) $request->get('top_priority', false);

        $topicName = $this->resolveTopicForSegments($segment_ids);
        if ($topicName === null) {
            return redirect()->back()
                ->withInput()
                ->with('error-message', 'Выберите ровно один сегмент для рассылки.');
        }

        $segment = Segment::getElement($segment_ids[0]);
        $sentCount = $segment->getMemberCountWithTokens();

        $sendId = DB::table('notification_sent_log')->insertGetId([
            'time' => now(),
            'article_id' => $article_id ?: null,
            'title' => $title,
            'message' => $message,
            'total' => $sentCount,
            'sent' => $sentCount,
            'topic_name' => $topicName,
            'segment_ids' => implode(',', $segment_ids),
            'send_method' => 'topic',
        ]);

        $data = ['send_id' => (string) $sendId];
        $image = '';
        if ($article_id) {
            $article = Article::find($article_id);
            if ($article) {
                $image = URLHelper::transform($article->image_small ?? $article->image);
                $data['article'] = (string) $article->id;
            } else {
                FirebaseLogger::warning('Article not found', ['article_id' => $article_id]);
            }
        }

        $result = $this->firebaseService->sendToTopic($topicName, $title, $message, $image, $data, $top_priority);

        FirebaseLogger::info('Отправка по топику', [
            'topic' => $topicName,
            'time' => now()->toDateTimeString(),
            'firebase_response' => [
                'success' => $result['success'] ?? false,
                'status' => $result['status'] ?? null,
                'response' => $result['response'] ?? $result['body'] ?? null,
            ],
        ]);

        $color = $label && isset($this->labels[$label]) ? $this->labels[$label] : null;
        CreateNotificationsForSegmentJob::dispatch(
            $segment_ids[0],
            $title,
            $article_id ?: null,
            $label ?: null,
            $color
        );

        return redirect()->route('notification.show')->with('success-message', 'Сообщение отправлено по топику.');
    }

    /**
     * Определяет топик для выбранных сегментов, если возможна отправка по топику.
     * Один сегмент «Все пользователи» (id=-1) - SendAll; один сегмент с firebase_topic - этот топик.
     */
    private function resolveTopicForSegments(array $segmentIds): ?string
    {
        if (count($segmentIds) !== 1) {
            return null;
        }
        $segment = Segment::getElement($segmentIds[0]);
        if ($segment->id === Segment::SEGMENT_ID_ALL_USERS) {
            return Segment::TOPIC_SEND_ALL;
        }
        return $segment->firebase_topic ?? null;
    }

    public function validateTokens(Request $request)
    {
        $tokens = Session::whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->unique()
            ->toArray();

        $chunks = array_chunk($tokens, self::TOKEN_LIMIT);

        foreach ($chunks as $index => $chunk) {
            $result = $this->firebaseService->validateTokens($chunk);
        }

        return redirect()->route('notification.show')->with('success-message', 'Валидация токенов завершена.');
    }
}
