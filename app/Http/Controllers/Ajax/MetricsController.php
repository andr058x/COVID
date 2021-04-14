<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use App\Support\CovidMetrics;
use Illuminate\Http\Request;

class MetricsController extends Controller
{
    public function __invoke()
    {
        $germanCovidMetrics = new CovidMetrics;

        // Here we generate the JSON that'll then be fetched using Axios
        // from the Vue.js component.
        return [
            'data' => [
                'newInfectionsInLastDay' => $germanCovidMetrics->getNewInfectionsInLastDay(),
                'totalInfections' => $germanCovidMetrics->getTotalInfections(),
                'infectionsIncreaseInLastDay' => $germanCovidMetrics->getInfectionsIncreaseInLastDay(),
                'averageInfectionsIncreaseInLastDays' => $germanCovidMetrics->getAverageInfectionsIncreaseInLastDays(),
                'averageInfectionsDecreaseInLastDays' => $germanCovidMetrics->getAverageInfectionsDecreaseInLastDays(),
                'numberOfDaysForAverage' => $germanCovidMetrics::NUMBER_OF_DAYS_FOR_AVERAGE,
                'incidenceValueForWholeGermany' => $germanCovidMetrics->getIncidenceValueForWholeGermany(),
                'targetTotalInfection' => $germanCovidMetrics->getTargetTotalInfection(),
                'predictionOfDaysOfLockdownStillRequired' => $germanCovidMetrics->getPredictionOfDaysOfLockdownStillRequired(),
            ],
        ];
    }
}
