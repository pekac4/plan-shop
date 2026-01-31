<?php

namespace App\Services\Highlights;

use App\Models\User;
use App\Repositories\Highlights\HighlightRepository;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class UpcomingChefService
{
    public function __construct(private HighlightRepository $highlightRepository) {}

    /**
     * @return array{leader: ?User, leaderRecipes: Collection, monthLabel: string}
     */
    public function build(): array
    {
        $monthStart = CarbonImmutable::now()->subMonthNoOverflow()->startOfMonth();
        $monthEnd = $monthStart->endOfMonth();

        $leader = $this->highlightRepository->upcomingLeader($monthStart, $monthEnd);

        return [
            'leader' => $leader,
            'leaderRecipes' => $leader
                ? $this->highlightRepository->upcomingLeaderRecipes($leader, $monthStart, $monthEnd)
                : collect(),
            'monthLabel' => $monthStart->format('F Y'),
        ];
    }
}
