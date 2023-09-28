<?php

namespace App\Repositories\TranslationRepository;

use App\Models\Translation;

class TranslationRepository extends \App\Repositories\CoreRepository
{

    protected function getModelClass()
    {
        return Translation::class;
    }
}
