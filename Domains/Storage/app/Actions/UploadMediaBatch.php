<?php

namespace Domains\Storage\Actions;

use Domains\Storage\Enums\MediaCategory;
use Domains\Storage\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UploadMediaBatch
{
    public function __construct(protected UploadMedia $uploadMedia)
    {
        //
    }

    /**
     * Upload several files and/or URLs in one call, attaching each as media to the
     * same resource. All-or-nothing: if any item fails, the DB rows and any files
     * already stored for this batch are rolled back rather than left partially applied.
     *
     * @param  UploadedFile[]  $files
     * @param  string[]  $urls
     * @param  array{disk?: string, category?: MediaCategory, title?: string, folder_id?: int, starred?: bool}  $options
     * @return Collection<int, Media>
     */
    public function handle(Model $resource, array $files, array $urls, array $options = []): Collection
    {
        return DB::transaction(function () use ($resource, $files, $urls, $options) {
            $uploaded = collect();

            try {
                foreach ($files as $file) {
                    $uploaded->push($this->uploadMedia->handle($resource, $file, $options));
                }

                foreach ($urls as $url) {
                    $uploaded->push($this->uploadMedia->handleFromUrl($resource, $url, $options));
                }
            } catch (Throwable $exception) {
                $uploaded->each(fn (Media $media) => Storage::disk($media->disk)->delete($media->path));

                throw $exception;
            }

            return $uploaded;
        });
    }
}
