<?php

namespace App\Repositories\TransactionRepository;

use App\Models\Transaction;

class TransactionRepository extends \App\Repositories\CoreRepository
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
        return Transaction::class;
    }

    public function paginate($perPage, $array = [])
    {
        return $this->model()->with([
            'payable.currency', 'user',
            'paymentSystem.translation' => fn($q) => $q->actualTranslation($this->lang)
        ])->filter($array)
            ->orderBy($array['column'] ?? 'id', $array['sort'] ?? 'desc')
            ->paginate($perPage);
    }

    public function show(int $id)
    {
        return $this->model()->with([
            'payable', 'user',
            'paymentSystem.translation' => fn($q) => $q->actualTranslation($this->lang)
        ])->find($id);
    }
}
