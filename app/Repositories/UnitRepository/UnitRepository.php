<?php

namespace App\Repositories\UnitRepository;

use App\Models\Unit;

class UnitRepository extends \App\Repositories\CoreRepository
{
    private $lang;

    public function __construct()
    {
        parent::__construct();
        $this->lang = $this->setLanguage();
    }

    protected function getModelClass()
    {
        return Unit::class;
    }

    /**
     * Get Units with pagination
     */
    public function unitsPaginate($perPage, $active = null, $array = [])
    {
       return $this->model()->with([
            'translation' => fn($q) => $q->actualTranslation($this->lang)
       ])
           ->when(isset($active), function ($q) use ($active) {
               $q->where('active', $active);
           })
           ->when(isset($array['search']), function ($q) use($array) {
               $q->whereHas('translations', function ($q) use($array) {
                   $q->where('title', 'LIKE', '%'. $array['search'] . '%');
               });
           })
           ->orderBy($array['column'] ?? 'id', $array['sort'] ?? 'desc')
           ->paginate($perPage);
    }

    /**
     * Get Unit by Identification
     */
    public function unitDetails(int $id)
    {
        return $this->model()->with([
            'translation' => fn($q) => $q->actualTranslation($this->lang)
        ])->find($id);
    }

}
