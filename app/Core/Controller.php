<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Base controller with shared helpers.
 *
 * @package App\Core
 */
abstract class Controller
{
    protected Request $request;

    public function __construct()
    {
        $this->request = new Request();
    }

    /**
     * Render a view with the app layout.
     *
     * @param array<string,mixed> $data
     */
    protected function view(string $view, array $data = [], string $layout = 'layouts/app'): void
    {
        View::display($view, $data, $layout);
    }

    /**
     * Render a view without the app chrome (e.g. auth pages).
     *
     * @param array<string,mixed> $data
     */
    protected function bare(string $view, array $data = []): void
    {
        View::display($view, $data, 'layouts/blank');
    }

    /**
     * @param array<string,mixed> $data
     */
    protected function json(array $data, int $status = 200): never
    {
        Response::json($data, $status);
    }

    /**
     * Ensure the current user is authenticated.
     */
    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            if ($this->request->wantsJson()) {
                Response::error('Unauthenticated.', [], 401);
            }
            flash('error', 'Please sign in to continue.');
            redirect('login');
        }
    }

    /**
     * Ensure the current user has a permission or abort.
     */
    protected function authorize(string $permission): void
    {
        if (!Auth::can($permission)) {
            if ($this->request->wantsJson()) {
                Response::error('You are not authorized to perform this action.', [], 403);
            }
            Response::abort(403);
        }
    }

    /**
     * Validate CSRF for state-changing requests.
     */
    protected function verifyCsrf(): void
    {
        Csrf::check($this->request);
    }

    /**
     * Validate request data; on failure redirect back or return JSON errors.
     *
     * @param array<string,string> $rules
     * @param array<string,string> $labels
     * @return array<string,mixed> Validated input (all input).
     */
    protected function validate(array $rules, array $labels = []): array
    {
        $validator = new Validator($this->request->all(), $rules, $labels);

        if ($validator->fails()) {
            if ($this->request->wantsJson()) {
                Response::error($validator->firstError() ?? 'Validation failed.', ['errors' => $validator->errors()], 422);
            }
            Session::flashOld($this->request->all());
            flash('errors', $validator->errors());
            flash('error', $validator->firstError());
            $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
            redirect($referer);
        }

        Session::clearOld();
        return $this->request->all();
    }
}
