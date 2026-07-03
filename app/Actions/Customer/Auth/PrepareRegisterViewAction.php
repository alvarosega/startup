<?php

declare(strict_types=1);

namespace App\Actions\Customer\Auth;

use App\Services\Customer\Queries\BranchQueryService;

final class PrepareRegisterViewAction
{
    public function __construct(
        private readonly BranchQueryService $branchQueryService
    ) {}

    /**
     * @param array<string, mixed> $queryParams
     * @return array{activeBranches: array<int, mixed>, collidingBranches: array<int, mixed>, availableAvatars: array<int, array{id: string, url: string}>}
     */
    public function execute(array $queryParams): array
    {
        $lat = isset($queryParams['latitude']) && $queryParams['latitude'] !== '' ? (float) $queryParams['latitude'] : null;
        $lng = isset($queryParams['longitude']) && $queryParams['longitude'] !== '' ? (float) $queryParams['longitude'] : null;

        $avatars = [];
        for ($i = 1; $i <= 8; $i++) {
            $avatars[] = [
                'id' => "avatar_{$i}.png",
                'url' => asset("assets/avatars/avatar_{$i}.png"),
            ];
        }

        return [
            'activeBranches' => $this->branchQueryService->getActiveBranches(),
            'collidingBranches' => ($lat !== null && $lng !== null)
                ? $this->branchQueryService->getCollidingBranches($lat, $lng)
                : [],
            'availableAvatars' => $avatars,
        ];
    }
}