<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class GoogleMaps extends BaseConfig
{
    /**
     * Google Maps JavaScript API Key
     * Set in .env as GOOGLE_MAPS_API_KEY=your_key_here
     */
    public string $apiKey = '';

    public function __construct()
    {
        parent::__construct();
        $this->apiKey = env('GOOGLE_MAPS_API_KEY', env('google.maps.apiKey', ''));
    }
}
