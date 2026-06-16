<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

/**
 * App\Models\Segment
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property array $card_ids
 * @property string|null $firebase_topic Название Firebase топика для этого сегмента
 * @property string|null $topic_subscription_status pending|in_progress|completed|failed
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|Segment where($column, $operator = null, $value = null, $boolean = 'and')
 * @mixin Builder
 */
class Segment extends Model
{
    protected $table = 'segment';
    protected $guarded = ['id'];
    protected $casts = [
        'card_ids' => 'array'
    ];

    const SEGMENT_ID_ALL_USERS = -1;

    /** Топик Firebase для рассылки «Все пользователи». */
    const TOPIC_SEND_ALL = 'SendAll';

    const TOPIC_SUBSCRIPTION_PENDING = 'pending';
    const TOPIC_SUBSCRIPTION_IN_PROGRESS = 'in_progress';
    const TOPIC_SUBSCRIPTION_COMPLETED = 'completed';
    const TOPIC_SUBSCRIPTION_FAILED = 'failed';

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(AppUser::class, 'segment_user', 'segment_id', 'user_id')
            ->withPivot('created_at');
    }

    /**
     * Количество пользователей сегмента, у которых есть хотя бы один FCM-токен (могут получить пуш).
     */
    public function getMemberCountWithTokens(): int
    {
        $userIds = $this->getMemberUserIds();
        if ($userIds->isEmpty()) {
            return 0;
        }
        return (int) \DB::table('session')
            ->whereIn('user_id', $userIds)
            ->whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->selectRaw('COUNT(DISTINCT user_id) as cnt')
            ->value('cnt');
    }

    /**
     * ID пользователей в сегменте (из segment_user или все для виртуального "Все пользователи").
     *
     * @return \Illuminate\Support\Collection<int, int>
     */
    public function getMemberUserIds()
    {
        if ($this->id === self::SEGMENT_ID_ALL_USERS) {
            return AppUser::whereNotNull('client_card_id')->pluck('id');
        }
        if (!Schema::hasTable('segment_user')) {
            return collect($this->card_ids ?? [])->isEmpty()
                ? collect()
                : AppUser::whereIn('client_card_id', $this->card_ids)->pluck('id');
        }
        return \DB::table('segment_user')->where('segment_id', $this->id)->pluck('user_id');
    }

    /**
     * Синхронизация участников сегмента из массива client_card_id (записывает в segment_user).
     */
    public function syncMembersFromCardIds(array $cardIds): void
    {
        if ($this->id === self::SEGMENT_ID_ALL_USERS || !Schema::hasTable('segment_user')) {
            return;
        }
        $cardIds = array_filter(array_unique($cardIds));
        $userIds = AppUser::whereIn('client_card_id', $cardIds)->pluck('id');
        \DB::table('segment_user')->where('segment_id', $this->id)->delete();
        $now = now()->toDateTimeString();
        foreach ($userIds->chunk(1000) as $chunk) {
            $rows = $chunk->map(fn ($userId) => [
                'segment_id' => $this->id,
                'user_id' => $userId,
                'created_at' => $now,
            ])->toArray();
            \DB::table('segment_user')->insert($rows);
        }
    }

    public static function getElement(int $id): Segment
    {
        return match ($id) {
            self::SEGMENT_ID_ALL_USERS => self::getAllUsersSegment(),
            default => self::find($id),
        };
    }

    /**
     * @return Collection|Segment[]
     */
    public static function getElements(): Collection
    {
        $collection = self::withCount('users')->get();
        $collection->add(self::getAllUsersSegment());

        return $collection;
    }

    private static function getAllUsersSegment(): Segment
    {
        $segment = new Segment();
        $segment->id = self::SEGMENT_ID_ALL_USERS;
        $segment->uuid = "";
        $segment->name = "Все пользователи";
        $segment->card_ids = [];
        $segment->firebase_topic = self::TOPIC_SEND_ALL;
        $segment->topic_subscription_status = null;
        $segment->created_at = now();
        $segment->updated_at = now();

        return $segment;
    }
}
