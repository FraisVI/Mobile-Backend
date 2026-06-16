<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\DeleteSegmentAfterUnsubscribeJob;
use App\Jobs\SubscribeSegmentToTopicJob;
use App\Jobs\UnsubscribeSegmentFromTopicJob;
use App\Models\AppUser;
use App\Models\Segment;
use App\Models\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SegmentController extends AdminController
{
    public function index() {
        $title = 'Сегменты пользователей';
        $segments = Segment::getElements();

        return view('admin.segments', compact('title', 'segments'));
    }

    public function create()
    {
        $title = 'Новый сегмент пользователей';
        $users = collect();
        $items = collect();
        $cardIds = old('card_ids', []);
        if (!empty($cardIds)) {
            $items = AppUser::whereIn('client_card_id', $cardIds)->get();
        }

        return view('admin.segment.add', compact('title', 'users', 'items'));
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:128',
            'firebase_topic' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9_-]+$/',
            ],
        ], [
            'firebase_topic.regex' => 'Топик может содержать только латиницу (a–z, A–Z), цифры, символы _ и -. Пробелы и другие символы запрещены.',
        ]);

        $segment = new Segment();
        $segment->fill($request->only(['name', 'firebase_topic']));
        $segment->uuid = '';
        $segment->card_ids = [];

        if (empty($segment->firebase_topic)) {
            $segment->save();
            $segment->firebase_topic = 'topic_' . $segment->id;
            $segment->save();
        } else {
            $segment->save();
        }

        $cardIds = $request->get('card_ids', []);
        if (!empty($cardIds)) {
            $segment->syncMembersFromCardIds($cardIds);
        }

        if ($segment->id > 0) {
            if (empty($segment->firebase_topic)) {
                $segment->firebase_topic = 'topic_' . $segment->id;
                $segment->save();
            }
            $segment->topic_subscription_status = Segment::TOPIC_SUBSCRIPTION_PENDING;
            $segment->save();
            SubscribeSegmentToTopicJob::dispatch($segment->id);
        }

        return redirect()->route('segments.index')->with('success-message', 'Сегмент успешно создан.');
    }

    public function edit($id)
    {
        $title = 'Редактирование сегмента';
        $segment = Segment::findOrFail($id);
        $users = collect();

        $items = \Schema::hasTable('segment_user')
            ? $segment->users()->get()
            : AppUser::whereIn('client_card_id', $segment->card_ids ?? [])->get();

        return view('admin.segment.edit', compact('title', 'segment', 'users', 'items'));
    }

    /**
     * Поиск пользователей для добавления в сегмент (AJAX). Не грузит всех пользователей.
     */
    public function searchUsers(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $query = AppUser::query()
            ->select('id', 'client_card_id', 'firstname', 'lastname', 'middlename', 'phone')
            ->where(function ($builder) use ($q) {
                $like = '%' . $q . '%';
                $builder->where('client_card_id', 'like', $like)
                    ->orWhere('firstname', 'like', $like)
                    ->orWhere('lastname', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            })
            ->limit(30);

        $users = $query->get()->map(function (AppUser $u) {
            return [
                'client_card_id' => $u->client_card_id,
                'short_fio'      => $u->shortFio(),
                'phone'          => $u->phone ?? '',
            ];
        });

        return response()->json($users->toArray());
    }

    public function update(Request $request, $id) {
        $request->validate([
            'name' => 'required|string|max:128',
        ]);

        /** @var \App\Models\Segment $segment */
        $segment = Segment::findOrFail($id);
        $segment->fill($request->only(['name']));
        $segment->save();

        $segment->syncMembersFromCardIds($request->get('card_ids', []));

        if ($segment->id > 0) {
            if (empty($segment->firebase_topic)) {
                $segment->firebase_topic = 'topic_' . $segment->id;
                $segment->save();
            }
            $segment->topic_subscription_status = Segment::TOPIC_SUBSCRIPTION_PENDING;
            $segment->save();
            SubscribeSegmentToTopicJob::dispatch($segment->id);
        }

        return redirect()->route('segments.index')->with('success-message', 'Сегмент успешно отредактирован.');
    }

    public function list(int $id) {
        $segment = Segment::getElement($id);
        $title = 'Сегмент: ' . $segment->name;

        $userIds = $segment->getMemberUserIds();
        $android = Session::where('uagent', 'LIKE', '%Android%')->whereNotNull('user_id')->get()->pluck('user_id')->toArray();
        $ios = Session::where('uagent', 'LIKE', '%iPhone%')->whereNotNull('user_id')->get()->pluck('user_id')->toArray();

        $users = AppUser::whereIn('id', $userIds)->get();
        $sessions = [];
        if ($userIds->isNotEmpty()) {
            foreach (Session::whereIn('user_id', $userIds)->whereNotNull('fcm_token')->get()->unique('user_id') as $s) {
                $sessions[$s->user_id] = $s;
            }
        }
        foreach ($users as $u) {
            $u->ios = in_array($u->id, $ios);
            $u->android = in_array($u->id, $android);
            $u->failed = 0;

            if (!array_key_exists($u->id, $sessions)) {
                $u->failed = 1;
            } else {
                if ($u->notify & AppUser::NOTIFY_SPECIAL) {
                    $u->failed = 2;
                }
            }
        }

        return view('admin.segment.list', compact('title', 'users', 'sessions'));
    }

    public function resubscribe($id)
    {
        $segment = Segment::findOrFail($id);

        if (empty($segment->firebase_topic)) {
            $segment->firebase_topic = 'topic_' . $segment->id;
        }

        $segment->topic_subscription_status = Segment::TOPIC_SUBSCRIPTION_PENDING;
        $segment->save();

        SubscribeSegmentToTopicJob::dispatch($segment->id);

        return redirect()->route('segments.index')->with('success-message', 'Подписка на топик перезапущена.');
    }

    public function destroy($id)
    {
        $id = (int) $id;
        if ($id === Segment::SEGMENT_ID_ALL_USERS) {
            return redirect()->route('segments.index')->with('error-message', 'Нельзя удалить виртуальный сегмент «Все пользователи».');
        }

        $segment = Segment::find($id);
        if (!$segment) {
            return redirect()->route('segments.index')->with('error-message', 'Сегмент не найден.');
        }

        UnsubscribeSegmentFromTopicJob::withChain([
            new DeleteSegmentAfterUnsubscribeJob($id),
        ])->dispatch($id);

        return redirect()->route('segments.index')->with(
            'success-message',
            'Сегмент поставлен в очередь на удаление: сначала отписка от топика, затем удаление.'
        );
    }
}
