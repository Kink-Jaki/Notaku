<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

abstract class Controller
{
    /**
     * Render the full page, or only the result fragment for realtime filter requests.
     *
     * @param  array<string, mixed>  $data
     * @return View|Response
     */
    protected function viewOrFragment(Request $request, string $view, string $fragment, array $data = [])
    {
        return $request->ajax()
            ? response()->view($fragment, $data)
            : view($view, $data);
    }
}
