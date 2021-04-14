<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class JohnHopkinsUniversityCovidData
{
    private function getCachingDuration(): \DateTime
    {
        return now()->addHour();
    }

    /**
     * Each item contains metrics per day.
     * Sorted by date (ascending).
     *
     * @return array
     */
    public function getMetricsItems(): array
    {
        // In order to increase the performance, we cache the COVID cases.
        return Cache::remember('german-covid-cases', $this->getCachingDuration(), function () {
            $metricItems = Http::get('https://pomber.github.io/covid19/timeseries.json')->json()['Germany'];

            foreach ($metricItems as $metricItemIndex => $metricItem) {
                // Here we precalculate the "currently_infected"
                // (number of confirmed cases - (number of deaths + number of recovered))
                $metricItems[$metricItemIndex]['currently_infected'] = $metricItems[$metricItemIndex]['confirmed']
                    - ($metricItems[$metricItemIndex]['deaths'] + $metricItems[$metricItemIndex]['recovered']);
            }

            foreach ($metricItems as $metricItemIndex => $metricItem) {
                // Here we precalculate the "confirmed_increase"
                // (confirmed for day N - confirmed for day N-1)
                $metricItems[$metricItemIndex]['confirmed_increase'] = $metricItemIndex === 0
                    ? $metricItems[$metricItemIndex]['confirmed']
                    : $metricItems[$metricItemIndex]['confirmed'] - $metricItems[$metricItemIndex - 1]['confirmed'];

                // Here we precalculate the "deaths_increase"
                // (deaths for day N - deaths for day N-1)
                $metricItems[$metricItemIndex]['deaths_increase'] = $metricItemIndex === 0
                    ? $metricItems[$metricItemIndex]['deaths']
                    : $metricItems[$metricItemIndex]['deaths'] - $metricItems[$metricItemIndex - 1]['deaths'];

                // Here we precalculate the "recovered_increase"
                // (recovered for day N - recovered for day N-1)
                $metricItems[$metricItemIndex]['recovered_increase'] = $metricItemIndex === 0
                    ? $metricItems[$metricItemIndex]['recovered']
                    : $metricItems[$metricItemIndex]['recovered'] - $metricItems[$metricItemIndex - 1]['recovered'];

                // Here we precalculate the "currently_infected" difference
                $currentlyInfectedDifference = $metricItemIndex === 0
                    ? $metricItems[$metricItemIndex]['currently_infected']
                    : $metricItems[$metricItemIndex]['currently_infected'] - $metricItems[$metricItemIndex - 1]['currently_infected'];

                // Here we precalculate the "currently_infected_increase"
                // (currently infected for day N - currently infected for day N-1)
                // We use `max()` to avoid negative values.
                $metricItems[$metricItemIndex]['currently_infected_increase'] = $currentlyInfectedDifference;

                // Here we precalculate the "currently_infected_decrease"
                // (- (currently infected for day N - currently infected for day N-1))
                // We use `max()` to avoid negative values.
                $metricItems[$metricItemIndex]['currently_infected_decrease'] = -$currentlyInfectedDifference;
            }

            return $metricItems;
        });
    }
}
