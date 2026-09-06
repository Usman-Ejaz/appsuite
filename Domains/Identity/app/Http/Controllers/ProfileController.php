<?php

namespace Domains\Identity\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\Identity\Http\Resources\ProfileResource;
use Domains\Identity\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Get Profile
     *
     * Returns the profile of the currently authenticated user, including their company, the
     * apps they have access to, and their effective permissions.
     */
    public function get(Request $request): ProfileResource
    {
        abort_if(! $request->user() instanceof User, 403);

        return ProfileResource::make($request->user()->load(['company', 'apps']));
    }
}
