<?php

namespace App\Services\PipePlanner;

use App\Models\PipePlanningProject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Ownership for planner projects. Authenticated customers own projects via
 * user_id. Guests own projects via a random token kept in their session; only
 * its sha256 hash is stored. A guest who logs in later claims their projects.
 */
class ProjectAccess
{
    protected const SESSION_KEY = 'pipe_planner.guest_token';

    public function guestTokenHash(Request $request, bool $create = false): ?string
    {
        $token = $request->session()->get(self::SESSION_KEY);
        if (! $token && $create) {
            $token = Str::random(64);
            $request->session()->put(self::SESSION_KEY, $token);
        }

        return $token ? hash('sha256', $token) : null;
    }

    public function ownerAttributes(Request $request): array
    {
        return $request->user()
            ? ['user_id' => $request->user()->id, 'guest_token_hash' => null]
            : ['user_id' => null, 'guest_token_hash' => $this->guestTokenHash($request, true)];
    }

    public function owns(Request $request, PipePlanningProject $project): bool
    {
        if ($project->user_id !== null) {
            return $request->user()?->id === $project->user_id;
        }
        $hash = $this->guestTokenHash($request);

        return $hash !== null && $project->guest_token_hash !== null && hash_equals($project->guest_token_hash, $hash);
    }

    public function authorize(Request $request, PipePlanningProject $project): void
    {
        // 404 rather than 403 so references cannot be probed.
        abort_unless($this->owns($request, $project), 404);
    }

    public function claimGuestProjects(Request $request): void
    {
        if (($user = $request->user()) && ($hash = $this->guestTokenHash($request))) {
            PipePlanningProject::where('guest_token_hash', $hash)->whereNull('user_id')
                ->update(['user_id' => $user->id, 'guest_token_hash' => null]);
        }
    }

    public function query(Request $request)
    {
        if ($request->user()) {
            $this->claimGuestProjects($request);

            return PipePlanningProject::where('user_id', $request->user()->id);
        }
        $hash = $this->guestTokenHash($request);

        return PipePlanningProject::where('guest_token_hash', $hash ?? '__none__')->whereNull('user_id');
    }

    /** Key used for per-day analysis rate limiting. */
    public function rateKey(Request $request): string
    {
        return 'pipe-planner-analysis:'.($request->user()?->id ? 'u'.$request->user()->id : 'ip'.$request->ip());
    }
}
