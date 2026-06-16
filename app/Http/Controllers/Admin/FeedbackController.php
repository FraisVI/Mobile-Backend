<?php

namespace App\Http\Controllers\Admin;
use App\Models\AppUser;
use App\Models\Feedback;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Google\Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FeedbackController extends AdminController
{
    const LOG_CHANNEL = 'feedback';

    public function index() {
        $title = 'Обратная связь';

        $res = DB::table('feedback')->select(DB::raw('MAX(id) as id'))->groupBy('user_id')->pluck('id');
        $feedbacks = Feedback::whereIn('id', $res)->orderBy('created_at', 'DESC');

        $users = [];
        foreach (AppUser::whereIn('id', $feedbacks->pluck('user_id'))->get() as $u) {
            $users[$u->id] = $u;
        }

        $feedbacks = $feedbacks->get();

        return view('admin.feedback', compact('title', 'feedbacks', 'users'));
    }

    public function Show($id) {
        $title = 'Обратная связь';

        $user = AppUser::find($id);
        $messages = Feedback::where('user_id', $id)->get();
        $session = Session::where('user_id', $user->id)->orderBy('lastused', 'DESC')->first();
        DB::table('feedback')->where('user_id', $user->id)->update(['viewed' => 1]);

        return view('admin.feedback.show', compact('title', 'messages', 'user', 'session'));
    }

    public function send(Request $request, $id)
    {
        $user = AppUser::findOrFail($id);
        $sessions = Session::where('user_id', $user->id)
            ->whereNotNull('fcm_token')
            ->get()
            ->unique('fcm_token')
            ->pluck('fcm_token')
            ->all();

        $message = new Feedback();
        $message->user_id = $user->id;
        $message->from_user = 0;
        $message->fill($request->all());
        $message->save();

        if (count($sessions) > 0) {
            try {
                $client = new Client();
                $client->setAuthConfig(storage_path('app/firebase-service-account.json'));
                $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
                $token = $client->fetchAccessTokenWithAssertion()['access_token'];

                $projectId = 'SOME_ID';
                $endpoint = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

                foreach ($sessions as $fcmToken) {
                    $body = [
                        'message' => [
                            'token' => $fcmToken,
                            'notification' => [
                                'title' => 'Ответ на ваше обращение',
                                'body'  => $message->message,
                            ],
                            'android' => [
                                'priority' => 'high',
                                'notification' => [
                                    'sound' => 'default',
                                ],
                            ],
                            'apns' => [
                                'payload' => [
                                    'aps' => [
                                        'sound' => 'default',
                                        'content-available' => 1,
                                    ],
                                ],
                            ],
                            'data' => [
                                'feedback' => '1',
                            ],
                        ],
                    ];

                    $response = Http::withToken($token)
                        ->post($endpoint, $body);

                    Log::channel(self::LOG_CHANNEL)->info([
                        'token' => $fcmToken,
                        'status' => $response->status(),
                        'response' => $response->json(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::channel(self::LOG_CHANNEL)->error([
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Internal server error'
                ], 500);
            }
        }

        return redirect()
            ->route('feedback.show', [$user->id])
            ->with('success-message', 'Сообщение успешно отправлено пользователю.');
    }

    public function file($filename): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return Storage::disk('private')->download($filename);
    }

    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        Feedback::destroy($id);
        return response()->json();
    }
}
