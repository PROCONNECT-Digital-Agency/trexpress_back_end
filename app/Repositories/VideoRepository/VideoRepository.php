<?php

namespace App\Repositories\VideoRepository;

use App\Models\ImportVideo;
use Illuminate\Support\Facades\DB;

class VideoRepository extends \App\Repositories\CoreRepository
{
    private $lang;

    /**
     * @param $lang
     */
    public function __construct()
    {
        parent::__construct();
        $this->lang = $this->setLanguage();
    }

    protected function getModelClass()
    {
        return ImportVideo::class;
    }

    /**
     * Get brands with pagination
     */
    public function videosPaginate($perPage, $active = null, $array = [])
    {
        return $this->model()
            ->whereHas('translation', function ($q) {
                $q->where('locale', $this->lang);
            })
            ->when(isset($array['type']), function ($q) use ($array) {
                $q->where('type', ImportVideo::TYPES[$array['type']]);
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->where('active', $active);
            })
            ->when(isset($array['published_at']), function ($q) {
                $q->whereNotNull('published_at');
            })
            ->orderBy($array['column'] ?? 'id', $array['sort'] ?? 'desc')
            ->paginate($perPage);
    }

    /**
     * Get brands with pagination
     */
    public function blogByUUID(string $uuid)
    {
        return $this->model()
            ->whereHas('translation', function ($q) {
                $q->where('locale', $this->lang);
            })
            ->with([
                'translation' => fn($q) => $q->where('locale', $this->lang)
            ])
            ->firstWhere('uuid', $uuid);
    }
}
