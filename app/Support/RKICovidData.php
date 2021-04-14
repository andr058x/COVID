<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RKICovidData
{
    private function getCachingDuration(): \DateTime
    {
        return now()->addHour();
    }

    public function getData(): array
    {
        return Cache::remember('rki-covid-data', $this->getCachingDuration(), function () {
            return Http::get('https://services7.arcgis.com/mOBPykOjAyBO2ZKk/arcgis/rest/services/Coronafälle_in_den_Bundesländern/FeatureServer/0/query?where=1%3D1&outFields=*&outSR=4326&f=json')->json();
        });
    }
}
