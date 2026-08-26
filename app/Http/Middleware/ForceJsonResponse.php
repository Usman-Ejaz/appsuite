<?php

namespace App\Http\Middleware;

use App\Enums\ApiErrorCode;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Response as ResponseFactory;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Normalizes every API response into one fixed envelope:
 * {status, message, code, data, meta, links}.
 *
 * Applied to the whole `api` middleware group, so controllers/Resources only need to
 * return a Resource/array/exception — this coerces whatever shape that produced into
 * the app-wide contract, including framework-default shapes it didn't author itself
 * (e.g. a stock ValidationException's {message, errors}, which folds into `data`).
 */
class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->headers->has('Accept')) {
            $request->headers->set('Accept', 'application/json');
        }

        $response = $next($request);

        // Content-less responses (204 delete actions, file downloads, etc.) are left
        // untouched — decoding/re-encoding an empty body would inject a JSON body into
        // what must remain a bodyless response, breaking assertNoContent()/HTTP semantics.
        if ($response->getStatusCode() === Response::HTTP_NO_CONTENT || $response->getContent() === '') {
            return $response;
        }

        if (Str::contains((string) $response->headers->get('Content-Type'), ['spreadsheetml.sheet', 'text/csv', 'text/plain', 'application/pdf'])) {
            return $response;
        }

        if (! $response instanceof JsonResponse) {
            $response = ResponseFactory::json(
                $response->getContent(),
                $response->getStatusCode(),
                $response->headers->all()
            );
        }

        $contents = json_decode($response->getContent(), true);
        $contents = Arr::wrap($contents);

        // Stable machine-readable error code: explicit body `code`, else the transport
        // header set by abort() call sites, else a generic default for 401/403. Pulled here
        // so it never leaks into `data`. The transport header is dropped from the response.
        $code = Arr::pull($contents, 'code')
            ?? $response->headers->get(ApiErrorCode::HEADER)
            ?? ApiErrorCode::defaultFor($response->getStatusCode());
        $response->headers->remove(ApiErrorCode::HEADER);

        if ($response->isSuccessful()) {
            $status = Arr::pull($contents, 'status', true);
            $message = Arr::pull($contents, 'message', 'Successful Response');

            // A body with no explicit `data` key (e.g. login/token endpoints, which
            // return their flat payload as the whole body) becomes `data` in full —
            // clear $contents so the leftovers-preservation spread below doesn't then
            // duplicate those same fields back onto the top level.
            if (array_key_exists('data', $contents)) {
                $data = Arr::pull($contents, 'data');
            } else {
                $data = $contents;
                $contents = [];
            }

            $meta = Arr::pull($contents, 'meta');
            $links = Arr::pull($contents, 'links');

            // Whatever's left wasn't one of the canonical envelope keys — e.g.
            // ApiKeyController deliberately returns `api_secret` as a sibling of `data`,
            // not nested inside it, so a generic dump of `data` never captures the
            // plaintext secret. Preserve those rather than silently discarding them.
            $leftovers = $contents;
        } else {
            $status = Arr::pull($contents, 'status', false);
            $message = Arr::pull($contents, 'message');
            $data = Arr::pull($contents, 'data') ?? Arr::pull($contents, 'errors');
            $meta = Arr::pull($contents, 'meta');
            $links = Arr::pull($contents, 'links');

            // Error bodies never preserve leftovers: a framework-rendered exception in
            // debug mode carries `exception`/`file`/`line`/`trace`, which must never
            // reach the client regardless of what triggered the error.
            $leftovers = [];
        }

        $response->setData([
            ...$leftovers,
            'status' => $status,
            'message' => $message,
            'code' => $code,
            'data' => $data,
            'meta' => $meta,
            'links' => $links,
        ]);

        if ($version = config('app.version_minor')) {
            $response->headers->set('X-API-Version', $version);
        }

        if ($build = config('app.build')) {
            $response->headers->set('X-Build', $build);
        }

        return $response;
    }
}
