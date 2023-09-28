<?php

namespace App\Repositories\PaymentRepository;

use App\Models\Payment;

class PaymentRepository extends \App\Repositories\CoreRepository
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
        return Payment::class;
    }

    public function paginate($array)
    {
        return $this->model()
            ->with([
                'translation' => fn($q) => $q->where('locale', $this->lang)->select('id','locale','title','payment_id')
            ])
            ->when(isset($array['active']), function ($q) use ($array) {
                $q->where('active', $array['active']);
            })
            ->select('id','tag','input','client_id')
            ->get();
    }

    public function paymentsList($array)
    {
        return $this->model()
            ->with([
                'translation' => fn($q) => $q->where('locale', $this->lang)
            ])
            ->when(isset($array['active']), function ($q) use ($array) {
                $q->where('active', $array['active']);
            })
            ->get();
    }

    public function paymentDetails(int $id)
    {
        return $this->model()->with([
            'translation' => fn($q) => $q->where('locale', $this->lang)
        ])
            ->find($id);
    }
}
