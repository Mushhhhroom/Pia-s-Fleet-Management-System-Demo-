<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    /**
     * Enforce role-based access control (RBAC) across application modules.
     * Arguments define allowed roles, e.g. ['admin', 'dispatcher']
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // 1. Verify user is authenticated
        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please sign in to access your enterprise portal.');
        }

        $userRole = $session->get('user_role') ?? 'dispatcher';

        // 2. Drivers must stay in the mobile PWA view unless accessing public/shared API
        if ($userRole === 'driver') {
            $uri = $request->getUri()->getPath();
            // Allow driver portal and API endpoints
            if (!str_contains($uri, 'driver') && !str_contains($uri, 'logout') && !str_contains($uri, 'api')) {
                return redirect()->to('/driver/trips')->with('error', 'Access restricted: Drivers are directed to the mobile driver portal.');
            }
        }

        // 3. If specific role arguments were defined for the route, check membership
        if (!empty($arguments)) {
            // Admin always has universal access
            if ($userRole === 'admin') {
                return;
            }

            // Normalize arguments (CI4 may pass ['admin,dispatcher'] or ['admin', 'dispatcher'])
            if (is_string($arguments)) {
                $arguments = explode(',', $arguments);
            } elseif (is_array($arguments) && count($arguments) === 1 && str_contains($arguments[0], ',')) {
                $arguments = explode(',', $arguments[0]);
            }
            $allowedRoles = array_map('trim', (array)$arguments);

            if (!in_array($userRole, $allowedRoles, true)) {
                // Route to appropriate default view based on role
                $redirectUrl = match ($userRole) {
                    'driver'      => '/driver/trips',
                    'maintenance' => '/maintenance',
                    'dispatcher'  => '/trips',
                    default       => '/',
                };

                return redirect()->to($redirectUrl)->with('error', 'Access Restricted: Your role (' . ucfirst($userRole) . ') does not have permission for this section.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No post-processing needed
    }
}
