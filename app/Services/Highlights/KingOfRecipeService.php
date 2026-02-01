<?php

namespace App\Services\Highlights;

use App\Models\User;
use App\Repositories\Highlights\HighlightRepository;
use Illuminate\Support\Collection;

class KingOfRecipeService
{
    public function __construct(private HighlightRepository $highlightRepository) {}

    /**
     * @return array{leader: ?User, leaderRecipes: Collection}
     */
    public function build(): array
    {
        $leader = $this->highlightRepository->kingLeader();

        return [
            'leader' => $leader,
            'leaderRecipes' => $leader
                ? $this->highlightRepository->kingLeaderRecipes($leader)
                : collect(),
        ];
    }
}
