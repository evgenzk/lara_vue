<?php

namespace App\Http\Controllers\API;

use App\Eloquent\User;
use Illuminate\Http\Request;
use App\Eloquent\UserIntegration;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Eloquent\UserIntegrationActivity;
use App\Contracts\UserIntegrationActivityRepository;

/**
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class UserIntegrationActivityController extends Controller
{
    /**
     * The user integration activities repository.
     *
     * @var UserIntegrationActivityRepository
     */
    protected UserIntegrationActivityRepository $activities;

    public function __construct(UserIntegrationActivityRepository $activities)
    {
        $this->activities = $activities;
    }

    /**
     * Display a listing of the resource.
     *
     * @param User $user
     * @param int $userIntegrationId
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function recent(Request $request, User $user, int $userIntegrationId): JsonResponse
    {
        /** @var UserIntegration $userIntegration */
        $userIntegration = (new UserIntegration())->findOrFail($userIntegrationId);

        return response()->json([
            'activities' => $this->activities->recent($userIntegration, $request->all())->toArray(),
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param User $user
     * @param int $userIntegrationId
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function filters(Request $request, User $user, int $userIntegrationId): JsonResponse
    {
        /** @var UserIntegration $userIntegration */
        $userIntegration = (new UserIntegration())->findOrFail($userIntegrationId);

        return response()->json([
            'availableFilters' => $this->activities->getFilters($userIntegration, $request->all()),
        ]);
    }

    /**
     * @param User $user
     * @param int $userIntegrationActivityId
     *
     * @return JsonResponse
     */
    public function show(User $user, int $userIntegrationActivityId): JsonResponse
    {
        /** @var UserIntegrationActivity $userIntegrationActivity */
        $userIntegrationActivity = (new UserIntegrationActivity())->findOrFail($userIntegrationActivityId);
        $userIntegrationActivity->append('failedJobId');

        return response()->json([
            'activity' => $userIntegrationActivity,
        ]);
    }
}
