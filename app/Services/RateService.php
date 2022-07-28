<?php

namespace App\Services;

use App\Interfaces\RateRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

/**
 * RateService class.
 * @property RateRepositoryInterface $rateRepository
 */
class RateService
{
    public function __construct(RateRepositoryInterface $rateRepository)
    {
        $this->rateRepository = $rateRepository;
    }

    /**
     * @param $userID
     * @return array
     */
    public function getRates($userID): array
    {
        try {
            $avgRate = $this->rateRepository->getAvgRateByUserID($userID);
        } catch (ModelNotFoundException $e) {
            Log::Error("unable to get avg rate for the user: '$userID'");
            $avgRate = 0;
        }

        try {
            $chartRates = $this->rateRepository->getChartRatesByUserID($userID);
        } catch (ModelNotFoundException $e) {
            Log::Error("unable to get chart rates for the user: $userID");
        }

        return ['avgRate' => $avgRate, 'chartRates' => $chartRates];
    }
}
