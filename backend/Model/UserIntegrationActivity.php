<?php

namespace App\Eloquent;

use Eloquent;
use App\Casts\Json;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * Class UserIntegrationActivity.
 *
 * @property int $id
 * @property int $user_integration_id
 * @property int $flow_id
 * @property int $application_id
 * @property bool $is_repeated
 * @property bool $is_debug
 * @property string $error_message
 * @property string|null $entity_id
 * @property string|null $job_uuid
 * @property string $event_data
 * @property string $trigger
 * @property string $direction
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 * @property int $userId
 * @property int $failedJobId
 * @property string $applicationName
 * @property string $flowName
 * @property UserIntegration $userIntegration
 * @property Flow $flow
 * @property Integration $application
 *
 * @method EloquentBuilder|UserIntegrationActivity byStatus(...$args)
 * @method EloquentBuilder|UserIntegrationActivity byDebugFlag(...$args)
 * @method EloquentBuilder|UserIntegrationActivity byRepeatedFlag(...$args)
 * @method EloquentBuilder|UserIntegrationActivity recent(...$args)
 * @method EloquentBuilder|UserIntegrationActivity filterActivities(...$args)
 * @mixin Eloquent
 *
 * @property-read string $application_name
 * @property-read int|null $failed_job_id
 * @property-read string $flow_name
 * @property-read string $user_id
 *
 * @method static EloquentBuilder|UserIntegrationActivity newModelQuery()
 * @method static EloquentBuilder|UserIntegrationActivity newQuery()
 * @method static \Illuminate\Database\Query\Builder|UserIntegrationActivity onlyTrashed()
 * @method static EloquentBuilder|UserIntegrationActivity query()
 * @method static \Illuminate\Database\Query\Builder|UserIntegrationActivity withTrashed()
 * @method static \Illuminate\Database\Query\Builder|UserIntegrationActivity withoutTrashed()
 */
class UserIntegrationActivity extends Model
{
    use SoftDeletes;

    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_WARNING = 'warning';
    const STATUS_PENDING = 'pending';
    const STATUS_DEBUG = 'debug';
    const STATUS_PAUSE = 'pause';

    const DIRECTION_SOURCE = 'source';
    const DIRECTION_TARGET = 'target';

    const TRIGGER_CRON = 'cron';
    const TRIGGER_WEBHOOK = 'webhook';
    const TRIGGER_FUNCTION = 'function';

    /**
     * {@inheritdoc}
     */
    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'error_message' => null,
        'event_data' => null,
        'trigger' => 'cron',
        'direction' => self::DIRECTION_SOURCE,
    ];

    /**
     * {@inheritdoc}
     */
    protected $dateFormat = 'U';

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'user_integration_id',
        'flow_id',
        'application_id',
        'error_message',
        'event_data',
        'trigger',
        'direction',
        'status',
        'job_uuid',
        'is_repeated',
        'is_debug',
        'entity_id',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'event_data' => Json::class,
    ];

    /**
     * {@inheritdoc}
     */
    protected $hidden = [
        'flow_id', 'job_uuid', 'updated_at', 'deleted_at',
    ];

    /**
     * {@inheritdoc}
     */
    protected $appends = [
        'flowName',
        'applicationName',
        'userId',
    ];

    /**
     * @return BelongsTo
     */
    public function userIntegration(): BelongsTo
    {
        return $this->belongsTo(UserIntegration::class);
    }

    /**
     * @return BelongsTo
     */
    public function flow(): BelongsTo
    {
        return $this->belongsTo(Flow::class, 'flow_id', 'id');
    }

    /**
     * @return BelongsTo
     */
    public function application(): BelongsTo
    {
        return $this->belongsTo(Integration::class, 'application_id', 'id');
    }

    /**
     * @return string
     */
    public function getFlowNameAttribute(): string
    {
        return optional($this->flow()->first('name'))->name;
    }

    /**
     * @return string
     */
    public function getApplicationNameAttribute(): string
    {
        return optional($this->application()->first('name'))->name ?? '';
    }

    /**
     * @return string
     */
    public function getUserIdAttribute(): string
    {
        return tenant('user')->id ?? '';
    }

    /**
     * @return int|null
     */
    public function getFailedJobIdAttribute(): ?int
    {
        if ($this->job_uuid) {
            /** @var FailedJob|null $failedJob */
            $failedJob = (new FailedJob())->byUuid($this->job_uuid)->first();
            if ($failedJob) {
                return $failedJob->id;
            }
        }

        return null;
    }

    /**
     * @param EloquentBuilder $builder
     * @param string $status
     *
     * @return EloquentBuilder
     */
    public function scopeByStatus(EloquentBuilder $builder, string $status): EloquentBuilder
    {
        return $builder->where($builder->qualifyColumn('status'), $status);
    }

    /**
     * @param EloquentBuilder $builder
     * @param bool $isDebug
     *
     * @return EloquentBuilder
     */
    public function scopeByDebugFlag(EloquentBuilder $builder, bool $isDebug): EloquentBuilder
    {
        return $builder->where($builder->qualifyColumn('is_debug'), $isDebug);
    }

    /**
     * @param EloquentBuilder $builder
     * @param bool $isRepeated
     *
     * @return EloquentBuilder
     */
    public function scopeByRepeatedFlag(EloquentBuilder $builder, bool $isRepeated): EloquentBuilder
    {
        return $builder->where($builder->qualifyColumn('is_repeated'), $isRepeated);
    }

    /**
     * @param EloquentBuilder $builder
     *
     * @return EloquentBuilder
     */
    public function scopeRecent(EloquentBuilder $builder): EloquentBuilder
    {
        return $builder->where(
            $builder->qualifyColumn('updated_at'),
            '>=',
            now()->subMonths(3)->startOfDay()->timestamp
        )->latest();
    }

    /**
     * @param EloquentBuilder $builder
     * @param array $filters
     *
     * @return EloquentBuilder
     */
    public function scopeFilterActivities(EloquentBuilder $builder, array $filters): EloquentBuilder
    {
        return $builder
            ->where(function (EloquentBuilder $builder) use ($filters) {
                $value = $filters['status'] ?? '';
                if ($value != '') {
                    $builder->where($builder->qualifyColumn('status'), $value);
                }
                $value = $filters['direction'] ?? '';
                if ($value != '') {
                    $builder->where($builder->qualifyColumn('direction'), $value);
                }
                $value = $filters['trigger'] ?? '';
                if ($value != '') {
                    $builder->where($builder->qualifyColumn('trigger'), $value);
                }
                $value = $filters['sku'] ?? '';
                if ($value != '') {
                    $builder->where('event_data', 'like', "%{$value}%");
                }
                $value = $filters['flowIds'] ?? [];
                if (! empty($value)) {
                    $builder->whereIn($builder->qualifyColumn('flow_id'), $value);
                }

                $end = $filters['end'] ?? '';
                $start = $filters['start'] ?? '';

                if ($end != '' && $start != '') {
                    $builder->whereBetween($builder->qualifyColumn('updated_at'), [$start / 1000, $end / 1000]);
                } elseif ($end != '') {
                    $builder->where($builder->qualifyColumn('updated_at'), '<=', $end / 1000);
                } elseif ($start != '') {
                    $builder->where($builder->qualifyColumn('updated_at'), '>=', $start / 1000);
                }
            });
    }

    /**
     * @return UserIntegrationFlow|EloquentBuilder|Model|object|null
     */
    public function getUserIntegrationFlow()
    {
        $userIntegrationActivity = $this;

        return (new UserIntegrationFlow())
            ->where(function (EloquentBuilder $builder) use ($userIntegrationActivity) {
                $builder->where(
                    $builder->qualifyColumn('user_integration_id'),
                    $userIntegrationActivity->user_integration_id
                )
                    ->where(
                        function (EloquentBuilder $builder) use ($userIntegrationActivity) {
                            $builder->where(
                                $builder->qualifyColumn('first_flow_id'),
                                $userIntegrationActivity->flow_id
                            )
                                ->orWhere(
                                    $builder->qualifyColumn('second_flow_id'),
                                    $userIntegrationActivity->flow_id
                                );
                        }
                    );
            })->first();
    }
}
