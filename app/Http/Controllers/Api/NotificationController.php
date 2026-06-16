<?php

namespace App\Http\Controllers\Api;

use App\Models\Article;
use App\Models\Notification;
use App\NotificationCounterService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;

class NotificationController extends ApiController
{
    public function Notifications(): \Illuminate\Http\JsonResponse
    {
        $lastVisit = $this->counters->getNotificationLastVisit();
        /** @var Notification[] $notifications */
        $notifications = Notification::where('user_id', $this->appUser->id)->orderBy('created_at', 'DESC')->get();
        foreach ($notifications as $n) {
            $n->date = $n->created_at->translatedFormat('j F Y H:i');

            if ($lastVisit) {
                if ($n->created_at > $lastVisit) {
                    $n->new = true;
                }
            }
        }

        $this->counters->resetForNotification();

        return response()->json($notifications, 200, [], JSON_UNESCAPED_UNICODE);
    }

}
