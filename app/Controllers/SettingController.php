<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Response;
use App\Models\Setting;

/**
 * Application settings.
 *
 * @package App\Controllers
 */
final class SettingController extends Controller
{
    private Setting $model;

    public function __construct()
    {
        parent::__construct();
        $this->model = new Setting();
    }

    public function index(): void
    {
        $this->authorize('settings.view');
        $this->view('settings.index', [
            'title'    => 'Settings',
            'settings' => $this->model->allKeyed(),
        ]);
    }

    public function update(): void
    {
        $this->authorize('settings.edit');
        $this->verifyCsrf();

        $fields = [
            'business_name'    => 'general',
            'business_email'   => 'general',
            'business_phone'   => 'general',
            'business_address' => 'general',
            'currency'         => 'general',
            'currency_symbol'  => 'general',
            'timezone'         => 'general',
            'invoice_prefix'   => 'invoice',
            'purchase_prefix'  => 'invoice',
            'tax_rate'         => 'tax',
            'vat_rate'         => 'tax',
            'language'         => 'appearance',
        ];

        foreach ($fields as $key => $group) {
            $this->model->put($key, $this->request->string($key), $group);
        }

        flash('success', 'Settings saved successfully.');
        redirect('settings');
    }

    public function saveTheme(): void
    {
        // Theme is a per-browser preference stored by app.js in localStorage.
        // Do not overwrite the global business setting when one user toggles it.
        $theme = $this->request->string('theme', 'light');
        $theme = in_array($theme, ['light', 'dark'], true) ? $theme : 'light';
        if ($this->request->wantsJson()) {
            Response::success('Theme preference accepted.', ['theme' => $theme]);
        }
        redirect('profile');
    }
}
