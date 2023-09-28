<?php

namespace App\Repositories\ExtraRepository;

use App\Models\ExtraGroup;

class ExtraGroupRepository extends \App\Repositories\CoreRepository
{
    private $lang;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->lang = $this->setLanguage();
    }

    protected function getModelClass()
    {
        return ExtraGroup::class;
    }

    public function extraGroupList($active = null, $array = [])
    {
        return $this->model()->whereHas('translation', function ($q) {
            $q->where('locale', $this->lang);
        })
            ->when(isset($array['valid']), function ($q){
                $q->whereHas('extraValues');
            })
            ->with([
                'translation' => fn($q) => $q->where('locale', $this->lang)
            ])
            ->when(isset($active), function ($q) use ($active) {
                $q->where('active', $active);
            })
            ->orderByDesc('id')
            ->get();
    }

    public function extraGroupDetails(int $id)
    {
        return $this->model->whereHas('translation', function ($q) {
            $q->where('locale', $this->lang);
        })
            ->with([
                'translation' => fn($q) => $q->where('locale', $this->lang)
            ])->find($id);
    }

}
