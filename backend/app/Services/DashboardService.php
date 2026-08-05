<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Gym;
use App\Models\Sport;
use App\Repositories\Contracts\ClientRepositoryInterface;
use App\Repositories\Contracts\CoachRepositoryInterface;
use App\Repositories\Contracts\ExpenseRepositoryInterface;
use App\Repositories\Contracts\SportRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(
        private readonly ClientRepositoryInterface $clients,
        private readonly CoachRepositoryInterface $coaches,
        private readonly SportRepositoryInterface $sports,
        private readonly ExpenseRepositoryInterface $expenses,
    ) {}

    public function summary(Gym $gym): array
    {
        $incomeBySport = $this->incomeBySport($gym);
        $totalIncome = round($incomeBySport->sum('income'), 2);
        $totalExpenses = round($this->expenses->sumForGym($gym), 2);
        $coachSalaries = round($this->coaches->sumSalariesForGym($gym), 2);
        $netProfit = round($totalIncome - $totalExpenses - $coachSalaries, 2);

        return [
            'gym_name' => $gym->name,
            'total_clients' => $this->clients->countForGym($gym),
            'total_coaches' => $this->coaches->countForGym($gym),
            'coach_salaries' => $coachSalaries,
            'total_income' => $totalIncome,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
        ];
    }

    public function registrationsChart(Gym $gym): array
    {
        $clients = $this->clients->allWithSportsForGym($gym);
        $total = $clients->count();

        $counts = array_fill_keys(Sport::PRICE_TIERS, 0);
        foreach ($clients as $client) {
            $counts[$client->registration_type]++;
        }

        return collect($counts)->map(fn ($count, $type) => [
            'type' => $type,
            'count' => $count,
            'percentage' => $total > 0 ? round($count / $total * 100, 1) : 0.0,
        ])->values()->all();
    }

    public function incomeBySportChart(Gym $gym): array
    {
        return $this->incomeBySport($gym)->all();
    }

    private function incomeBySport(Gym $gym): Collection
    {
        $sports = $this->sports->allForGym($gym);
        $income = array_fill_keys($sports->pluck('id')->all(), 0.0);

        $activeClients = $this->clients->allWithSportsForGym($gym)
            ->filter(fn (Client $client) => Carbon::parse($client->registration_end)->startOfDay()->isAfter(Carbon::today()));

        foreach ($activeClients as $client) {
            foreach ($client->sports as $sport) {
                if (array_key_exists($sport->id, $income)) {
                    $income[$sport->id] += $this->priceForRegistrationType($sport, $client->registration_type);
                }
            }
        }

        return $sports->map(fn (Sport $sport) => [
            'sport' => $sport->name,
            'income' => round($income[$sport->id], 2),
        ])->values();
    }

    private function priceForRegistrationType(Sport $sport, string $registrationType): float
    {
        return $sport->priceFor($registrationType) ?? 0.0;
    }
}
