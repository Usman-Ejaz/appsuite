<?php

namespace Domains\Storage\Repositories;

use Domains\Core\Repositories\BaseRepository;
use Domains\Storage\Actions\UploadMediaBatch;
use Domains\Storage\Enums\MediaCategory;
use Domains\Storage\Models\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class MediaRepository extends BaseRepository
{
    protected string $model = Media::class;

    public function __construct(protected UploadMediaBatch $uploadMediaBatch)
    {
        //
    }

    /**
     * Upload one or more files and/or download one or more URLs, attaching each as
     * media to the given resource.
     *
     * @param  UploadedFile[]  $files
     * @param  string[]  $urls
     * @param  array{disk?: string, category?: MediaCategory, title?: string, folder_id?: int, starred?: bool}  $options
     * @return Collection<int, Media>
     */
    public function upload(Model $resource, array $files, array $urls, array $options = []): Collection
    {
        return $this->uploadMediaBatch->handle($resource, $files, $urls, $options);
    }

    /**
     * Delete a media record and its underlying file.
     *
     * @param  mixed  $id  The ID of the record to delete.
     */
    public function delete($id)
    {
        return $this->dbTransaction(function () use ($id) {
            $record = $this->findOrFail($id);

            Storage::disk($record->disk)->delete($record->path);

            return $record->delete();
        });
    }
}
