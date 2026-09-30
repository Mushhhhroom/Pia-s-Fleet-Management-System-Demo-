<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Authentication Routes
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::authenticate');
$routes->get('logout', 'AuthController::logout');

// Public RESTful APIs for IoT Telematics, Mapping System & Fleet Integration
$routes->group('api/v1', static function ($routes) {
    // CORS Preflight
    $routes->options('(:any)', 'ApiController::optionsHandler');

    // Fleet Asset Registry
    $routes->get('vehicles', 'ApiController::getVehicles');
    $routes->get('vehicles/(:num)', 'ApiController::getVehicle/$1');

    // Core Real-Time Mapping & Telematics Endpoints
    $routes->get('tracking', 'ApiController::getLiveTracking');
    $routes->get('map', 'ApiController::getLiveTracking');
    $routes->get('map/vehicles', 'ApiController::getLiveTracking');
    $routes->get('tracking/(:num)', 'ApiController::getVehicleTracking/$1');
    $routes->get('tracking/(:num)/trail', 'ApiController::getVehicleTrail/$1');
    $routes->post('tracking/simulate-step', 'ApiController::simulateStep');

    // Route Geometry & Road Distance Calculation API
    $routes->match(['get', 'post'], 'route', 'ApiController::calculateRoute');

    // Telemetry Ingestion & Log History
    $routes->get('telemetry', 'ApiController::getTelemetry');
    $routes->post('telemetry', 'ApiController::ingestTelemetry');
    $routes->get('trips/active', 'ApiController::getActiveTrips');
});

// Authenticated Application Routes (Protected by Auth and Role RBAC)
$routes->group('', ['filter' => 'auth'], static function ($routes) {

    // General Operations (Admin, Dispatcher, Maintenance)
    $routes->group('', ['filter' => 'role:admin,dispatcher,maintenance'], static function ($routes) {
        $routes->get('/', 'DashboardController::index');
        $routes->get('dashboard', 'DashboardController::index');
        $routes->get('tracking', 'TrackingController::index');
        $routes->get('tracking/live-data', 'TrackingController::liveData');
        $routes->post('tracking/simulate-step', 'TrackingController::simulateStep');
        $routes->get('vehicles', 'VehicleController::index');
        $routes->get('vehicles/new', 'VehicleController::create');
        $routes->post('vehicles', 'VehicleController::store');
        $routes->get('vehicles/(:num)', 'VehicleController::show/$1');
        $routes->get('vehicles/(:num)/edit', 'VehicleController::edit/$1');
        $routes->post('vehicles/(:num)', 'VehicleController::update/$1');
        $routes->post('vehicles/(:num)/delete', 'VehicleController::delete/$1');
    });

    // Dispatch & Personnel Operations (Admin, Dispatcher)
    $routes->group('', ['filter' => 'role:admin,dispatcher'], static function ($routes) {
        // Drivers & Operators
        $routes->get('drivers', 'DriverController::index');
        $routes->get('drivers/new', 'DriverController::create');
        $routes->post('drivers', 'DriverController::store');
        $routes->get('drivers/(:num)', 'DriverController::show/$1');
        $routes->get('drivers/(:num)/edit', 'DriverController::edit/$1');
        $routes->post('drivers/(:num)', 'DriverController::update/$1');
        $routes->post('drivers/(:num)/delete', 'DriverController::delete/$1');

        // Trips & Logistics Dispatch
        $routes->get('trips', 'TripController::index');
        $routes->get('trips/new', 'TripController::create');
        $routes->post('trips', 'TripController::store');
        $routes->get('trips/(:num)', 'TripController::show/$1');
        $routes->post('trips/(:num)/status', 'TripController::updateStatus/$1');

        // Fuel & Energy Logging
        $routes->get('fuel', 'FuelController::index');
        $routes->get('fuel/new', 'FuelController::create');
        $routes->post('fuel', 'FuelController::store');
    });

    // Technical Maintenance & Workshop (Admin, Maintenance)
    $routes->group('', ['filter' => 'role:admin,maintenance'], static function ($routes) {
        $routes->get('maintenance', 'MaintenanceController::index');
        $routes->get('maintenance/new', 'MaintenanceController::create');
        $routes->post('maintenance', 'MaintenanceController::store');
        $routes->post('maintenance/(:num)/status', 'MaintenanceController::updateStatus/$1');
    });

    // Executive Intelligence & Audits (Admin only)
    $routes->group('', ['filter' => 'role:admin'], static function ($routes) {
        $routes->get('reports', 'Reports::index');
        $routes->get('reports/export', 'Reports::exportCsv');
    });

    // Driver Mobile PWA (SDD §6.2)
    $routes->get('driver/trips', 'DriverController::mobileApp');
    $routes->post('driver/incident', 'DriverController::reportIncident');

    // Uploaded Receipts & Documents
    $routes->get('uploads/receipts/(:segment)', 'FuelController::viewReceipt/$1');
});

// Driver PWA & Telematics API Endpoints (SDD §6.2 & §5.4)
$routes->group('api/v1', static function ($routes) {
    $routes->get('driver/trips', 'Api\TripApi::index');
    $routes->post('driver/trips/(:num)/start', 'Api\TripApi::start/$1');
    $routes->post('driver/trips/(:num)/complete', 'Api\TripApi::complete/$1');
    $routes->post('driver/fuel', 'Api\FuelApi::log');
    $routes->post('driver/incident', 'DriverController::reportIncident');
    $routes->match(['get', 'post'], 'gps/ping', 'Api\GpsApi::ping');
});
