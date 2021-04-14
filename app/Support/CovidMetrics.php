<?php

namespace App\Support;

use Illuminate\Support\Arr;

class CovidMetrics
{
    const NUMBER_OF_DAYS_FOR_AVERAGE = 14;

    // The target incidence is defined by german government.
    const INCIDENCE_TARGET = 35;

    public function getJohnHopkinsUniversityCovidMetricsItems(): array
    {
        return (new JohnHopkinsUniversityCovidData)->getMetricsItems();
    }

    public function getRKICovidData(): array
    {
        return (new RKICovidData)->getData();
    }

    public function getJohnHopkinsUniversityLastDayCovidMetricsItem(): array
    {
        return Arr::last($this->getJohnHopkinsUniversityCovidMetricsItems());
    }

    public function getRKILastDayCovidMetricsItem(): array
    {
        return $this->getRKICovidData()['features'][0]['attributes'];
    }

    /**
     * 1a. New infections in the last 24h
     *
     * (doesn't consider deaths & recovered)
     *
     * @return int
     */
    public function getNewInfectionsInLastDay(): int
    {
        return $this->getJohnHopkinsUniversityLastDayCovidMetricsItem()['confirmed_increase'];
    }

    /**
     * 1b. Total infections
     *
     * The total number of infections is calculated the following way:
     * totalInfections = confirmed - (deaths + recovered)
     *
     * @return int
     */
    public function getTotalInfections(): int
    {
        return $this->getJohnHopkinsUniversityLastDayCovidMetricsItem()['currently_infected'];
    }

    /**
     * 1c. Increase of infections in the last 24h
     *
     * (considers deaths & recovered)
     *
     * @return int
     */
    public function getInfectionsIncreaseInLastDay(): int
    {
        return $this->getJohnHopkinsUniversityLastDayCovidMetricsItem()['currently_infected_increase'];
    }

    /**
     * 1d. Average increase of the last N days
     *
     * @return int
     */
    public function getAverageInfectionsIncreaseInLastDays(): int
    {
        return collect($this->getJohnHopkinsUniversityCovidMetricsItems())
            // Here we slice the metric items for the specified number of days.
            ->slice(-self::NUMBER_OF_DAYS_FOR_AVERAGE)
            // Here we find the average value by the "currently_infected_increase" field.
            ->avg('currently_infected_increase');
    }

    public function getAverageInfectionsDecreaseInLastDays(): int
    {
        return collect($this->getJohnHopkinsUniversityCovidMetricsItems())
            // Here we slice the metric items for the specified number of days.
            ->slice(-self::NUMBER_OF_DAYS_FOR_AVERAGE)
            // Here we find the average value by the "currently_infected_decrease" field.
            ->avg('currently_infected_decrease');
    }

    /**
     * Total population for all federal states.
     *
     * @return int
     */
    public function getPopulationForAllFederalStates(): int
    {
        return collect($this->getRKICovidData()['features'])
            // Here we get the population for each federal state.
            ->map(function (array $feature) {
                return $feature['attributes']['LAN_ew_EWZ'];
            })
            // Here we sum the population for all federal states.
            ->sum();
    }

    /**
     * Total cases for all federal states.
     *
     * @return int
     */
    public function getTotalCasesForAllFederalStates(): int
    {
        return collect($this->getRKICovidData()['features'])
            // Here we get the total cases for each federal state.
            ->map(function (array $feature) {
                return $feature['attributes']['Fallzahl'];
            })
            // Here we sum the cases for all federal states.
            ->sum();
    }

    /**
     * 2a. Incidence value for whole Germany per 100.000 persons
     *
     * The incidence rate definition is the number of new cases of a disease
     * divided by the number of persons at risk of the disease.
     * (number of new cases / number of persons at risk)
     *
     * @return int
     */
    public function getIncidenceValueForWholeGermany(): int
    {
        return $this->getTotalCasesForAllFederalStates() / $this->getPopulationForAllFederalStates() * 100000;
    }

    /**
     * 2b. Target total infection
     *
     * For the calculation, we make the following assumptions (without discussing their mathematical inaccuracies!)
     *
     * The target total infection for a incidence target is obtained as follows:
     * totalTarget = (totalCurrent / incidenceCurrent) * incidenceTarget (e.g., 35).
     * where:
     * totalCurrent = number of currently infected individuals (see 1b).
     * incidenceCurrent = current incidence-value (see 2a)
     *
     * @return int
     */
    public function getTargetTotalInfection(): int
    {
        return ($this->getTotalInfections() / $this->getIncidenceValueForWholeGermany()) * self::INCIDENCE_TARGET;
    }

    /**
     * 2c. Prediction of the days of lockdown still required until a defined incidence is reached.
     *
     * For the calculation, we make the following formula (without discussing their mathematical inaccuracies!)
     *
     * days = (totalCurrent - totalTarget) / decreaseAverage with:
     * totalCurrent and totalTarget defined as above
     * decreaseAverage = average decrease of new infections over n days (e.g., 7 days)
     *
     * @return int
     */
    public function getPredictionOfDaysOfLockdownStillRequired(): int
    {
        return ($this->getTotalInfections() - $this->getTargetTotalInfection()) / $this->getAverageInfectionsDecreaseInLastDays();
    }
}
