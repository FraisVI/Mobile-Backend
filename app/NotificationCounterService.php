<?php

namespace App;
use App\Models\AppUser;
use App\Models\Article;
use App\Models\Feedback;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class NotificationCounterService
{
    const TABLE = 'notification_counter';

    var $user_id;
    function __construct(AppUser $user) {
        $this->user_id = $user->id;
    }

    private function updateTimestamp($field) {
        DB::table(self::TABLE)->where('user_id', $this->user_id)->update([
            $field => DB::raw('NOW()')
        ]);
    }

    private function getLastVisitTimestamp($field): ?Carbon
    {
        $result = DB::table(self::TABLE)->where('user_id', $this->user_id)->first();
        return $result ? Carbon::parse($result->{$field}) : null;
    }

    function getNotificationCounter(): array
    {
        $counters = DB::table(self::TABLE)->where('user_id', $this->user_id)->first();

        if (!$counters) {
            DB::table(self::TABLE)->insert([
                'user_id' => $this->user_id,
                'notification' => DB::raw('NOW()'),
                'promotion' => DB::raw('NOW()'),
                'feedback' => DB::raw('NOW()'),
            ]);

            return [0, 0, 0];
        } else {
            $notification = Notification::where('user_id', $this->user_id)->where('created_at', '>', $counters->notification)->count();
            $promotion = Article::whereIn('type_id', [2,3,4])->where('published', 1)->where('created_at', '>', $counters->promotion)->count();
            $feedback = Feedback::where('user_id', $this->user_id)->where('from_user', 0)->where('created_at', '>', $counters->feedback)->count();

            return [$notification, $promotion, $feedback];
        }
    }

    function resetForNotification() {
        $this->updateTimestamp('notification');
    }

    function resetForPromotion() {
        $this->updateTimestamp('promotion');
    }

    function resetForFeedback() {
        $this->updateTimestamp('feedback');
    }

    function getNotificationLastVisit(): ?Carbon
    {
        return $this->getLastVisitTimestamp('notification');
    }

    function getPromotionsLastVisit(): ?Carbon
    {
        return $this->getLastVisitTimestamp('promotion');
    }

    function getFeedbackLastVisit(): ?Carbon
    {
        return $this->getLastVisitTimestamp('feedback');
    }
}
