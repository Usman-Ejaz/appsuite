<?php

namespace Domains\Storage\Actions;

use Domains\Storage\Enums\MediaCategory;
use Domains\Storage\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use InvalidArgumentException;

class UploadMedia
{
    /**
     * Download a remote file and attach it as media to a resource.
     *
     * @param  array{disk?: string, category?: MediaCategory, title?: string, folder_id?: int, starred?: bool}  $options
     */
    public function handleFromUrl(Model $resource, string $url, array $options = []): Media
    {
        $response = Http::get($url);

        $name = basename(parse_url($url, PHP_URL_PATH) ?: '') ?: Str::random(40);

        $tempPath = tempnam(sys_get_temp_dir(), 'media');
        file_put_contents($tempPath, $response->body());

        $file = new UploadedFile($tempPath, $name, $response->header('Content-Type') ?: null, null, true);

        try {
            return $this->handle($resource, $file, $options);
        } finally {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    /**
     * Store the given file on disk and attach it as media to a resource.
     *
     * @param  array{disk?: string, category?: MediaCategory, title?: string, folder_id?: int, starred?: bool}  $options
     */
    public function handle(Model $resource, UploadedFile $file, array $options = []): Media
    {
        // Media must resolve to a servable URL (see Media::getUrlAttribute()), so it defaults to
        // the `public` disk specifically rather than the app's general-purpose default disk
        // (`filesystems.default`, typically `local`) — `local` has no `url` config, so
        // FilesystemAdapter::getLocalUrl() silently falls back to a scheme/host-less relative
        // path (`/storage/{path}`) that only resolves correctly from the API's own origin.
        $disk = $options['disk'] ?? 'public';
        $category = $options['category'] ?? null;

        $this->assertValid($file, $category);

        $path = $file->store($this->directory($resource, $category), $disk);
        $fileName = $file->getClientOriginalName();

        return $resource->morphMany(Media::class, 'resource')->create([
            'folder_id' => $options['folder_id'] ?? null,
            'title' => $options['title'] ?? null,
            'starred' => $options['starred'] ?? false,
            'category' => $category?->value,
            'disk' => $disk,
            'file_name' => $fileName,
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    /**
     * Guard against callers that bypass HTTP validation (console commands, other
     * Actions, ...) uploading a file that doesn't fit the given category's rules.
     */
    protected function assertValid(UploadedFile $file, ?MediaCategory $category): void
    {
        if (! $category) {
            return;
        }

        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, $category->allowedExtensions(), true)) {
            throw new InvalidArgumentException("The file extension \"{$extension}\" is not allowed for the {$category->value} category.");
        }

        $sizeInKilobytes = $file->getSize() / 1024;

        if ($sizeInKilobytes > $category->maxSizeInKilobytes()) {
            throw new InvalidArgumentException("The file exceeds the maximum size allowed for the {$category->value} category.");
        }
    }

    /**
     * The directory an upload should be stored under:
     * `{company_slug}/{resource_slug_or_id}/{category_slug}`.
     */
    protected function directory(Model $resource, ?MediaCategory $category): string
    {
        $companySlug = $resource->company?->slug ?? 'company_'.$resource->company_id;
        $categorySlug = Str::slug($category?->value ?? 'uncategorized');
        $resourceSlug = $resource->slug ?? class_basename($resource).'_'.$resource->getKey();

        return "{$companySlug}/{$resourceSlug}/{$categorySlug}";
    }
}
