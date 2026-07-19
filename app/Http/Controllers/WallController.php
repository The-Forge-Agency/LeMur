<?php

namespace App\Http\Controllers;

use App\Models\Wall;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WallController extends Controller
{
    public function show(Wall $wall): View
    {
        return view('walls.show', [
            'wall' => $wall,
            'isAdmin' => (bool) session()->get("wall_admin_{$wall->id}", false),
        ]);
    }

    public function manage(Request $request, Wall $wall): View
    {
        abort_unless(hash_equals($wall->admin_token, (string) $request->query('k')), 403);

        session()->put("wall_admin_{$wall->id}", true);

        return view('walls.manage', [
            'wall' => $wall,
            'justCreated' => $request->boolean('bienvenue'),
        ]);
    }
}
