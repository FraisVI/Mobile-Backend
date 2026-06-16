<?php

namespace App\Http\Controllers\Api;

use App\Models\AppUser;
use App\Models\Article;
use App\Models\Session;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Contract\Messaging;

class TestController extends ApiController
{
    var Messaging $messaging;

    public function __construct(Messaging $messaging)
    {
        $this->messaging = $messaging;
    }

    /**
     * @throws \Kreait\Firebase\Exception\MessagingException
     * @throws \Kreait\Firebase\Exception\FirebaseException
     */
    public function FcmSend($id): \Illuminate\Http\JsonResponse
    {
        $users = Session::whereNotNull('fcm_token')->get();

        $tokens = [];
        foreach ($users as $user) {
            $tokens[] = $user->fcm_token;
        }
        /** @var Article $article */
        $article = Article::find($id);

        $message = CloudMessage::new();
        $message = $message->withNotification(Notification::create($article->title, $article->subtitle, $article->image))
            ->withData(['article' => $article->id]);

        $result = $this->messaging->sendMulticast($message, $tokens);

        return response()->json($result);
    }

}
