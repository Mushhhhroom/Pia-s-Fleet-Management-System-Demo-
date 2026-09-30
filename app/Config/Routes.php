<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Authentication Routes
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::authenticate');
$routes->get('logout', 'AuthController::logout');

// Public RESTful APIs for IoT Telematics & Fleet Integration
$routes->group('api/v1', static function ($routes) {
    $routes->get('vehicles', 'ApiController::getVehicles');
    $routes->get('vehicles/(:num)', 'ApiController::getVehicle/$1');
    $routes->post('telemetry', 'ApiController::ingestTelemetry');
    $routes->get('trips/active', 'ApiController::getActiveTrips');
});

// Authenticated Application Routes
$routes->group('', ['filter' => 'auth'], static function ($routes) {
    // Dashboard
    $routes->get('/', 'DashboardController::index');
    $routes->get('dashboard', 'DashboardController::index');

    // Live GPS Telematics & Tracking
    $routes->get('tracking', 'TrackingController::index');
    $routes->get('tracking/live-data', 'TrackingController::liveData');
    $routes->post('tracking/simulate-step', 'TrackingController::simulateStep');

    // Fleet Vehicles Inventory
    $routes->get('vehicles', 'VehicleController::index');
    $routes->get('vehicles/new', 'VehicleController::create');
    $routes->post('vehicles', 'VehicleController::store');
    $routes->get('vehicles/(:num)', 'VehicleController::show/$1');
    $routes->get('vehicles/(:num)/edit', 'VehicleController::edit/$1');
    $routes->post('vehicles/(:num)', 'VehicleController::update/$1');
    $routes->post('vehicles/(:num)/delete', 'VehicleController::delete/$1');

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

    // Maintenance & Work Orders
    $routes->get('maintenance', 'MaintenanceController::index');
    $routes->get('maintenance/new', 'MaintenanceController::create');
    $routes->post('maintenance', 'MaintenanceController::store');
    $routes->post('maintenance/(:num)/status', 'MaintenanceController::updateStatus/$1');

    // Fuel & Energy Logging
    $routes->get('fuel', 'FuelController::index');
    $routes->get('fuel/new', 'FuelController::create');
    $routes->post('fuel', 'FuelController::store');

    // Reports & Analytics (SDD §5.7)
    $routes->get('reports', 'Reports::index');
    $routes->get('reports/export', 'Reports::exportCsv');

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
    $routes->post('gps/ping', 'Api\GpsApi::ping');
});
