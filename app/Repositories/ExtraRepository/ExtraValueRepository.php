<?php

namespace App\Repositories\ExtraRepository;

use App\Models\ExtraValue;

class ExtraValueRepository extends \App\Repositories\CoreRepository
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
        return ExtraValue::class;
    }

    public function extraValueList($active = null, $group = null,int $perPage = null, $search = null)
    {
        return $this->model->with([
            'group.translation' => function($q) {
                $q->actualTranslation($this->lang);
            }
            ])
            ->when(isset($search), function ($q) use ($search) {
                $q->where(function($query) use ($search) {
                    $query->where('value', 'LIKE', '%'. $search . '%');
                });
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->where('active', $active);
            })
            ->when(isset($group), function ($q) use ($group) {
                $q->where('extra_group_id', $group);
            })
            ->orderByDesc('id')
            ->get();
    }

    public function extraValueDetails(int $id)
    {
        return $this->model->with([
            'galleries' => function($q) {
                $q->select('id', 'type', 'loadable_id', 'path', 'title');
            },
            'group.translation' => function($q) {
                $q->actualTranslation($this->setLanguage());
            }
        ])->find($id);
    }

}
