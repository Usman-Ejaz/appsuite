<?php

namespace Domains\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\Identity\Http\Resources\ProfileResource;
use Domains\Identity\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Return the currently authenticated user's own profile.
     */
    public function get(Request $request): ProfileResource
    {
        abort_if(! $request->user() instanceof User, 403);
        info("Hello");
        return ProfileResource::make($request->user()->load(['company', 'apps']));
    }
}
