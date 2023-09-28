<?php

namespace App\Repositories\BlogRepository;

use App\Models\Blog;
use Illuminate\Support\Facades\DB;

class BlogRepository extends \App\Repositories\CoreRepository
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
        return Blog::class;
    }

    /**
     * Get brands with pagination
     */
    public function blogsPaginate($perPage, $active = null, $array = [])
    {
        return $this->model()
            ->whereHas('translation', function ($q) {
                $q->where('locale', $this->lang);
            })
            ->with([
                'translation' => fn($q) => $q->where('locale', $this->lang)
                    ->select('id', 'locale', 'blog_id', 'title', 'short_desc')
            ])
            ->when(isset($array['type']), function ($q) use ($array) {
                $q->where('type', Blog::TYPES[$array['type']]);
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
