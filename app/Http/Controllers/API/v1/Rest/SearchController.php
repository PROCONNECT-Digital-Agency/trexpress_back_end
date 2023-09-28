<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Http\Controllers\Controller;
use App\Http\Requests\Search\SearchRequest;
use App\Repositories\SearchRepository\SearchRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SearchController extends Controller
{
    private SearchRepository $repository;

    public function __construct(SearchRepository $repository)
    {
        $this->repository = $repository;
    }

    public function searchAll(SearchRequest $request): Collection
    {
        $collection = $request->validated();

        return $this->repository->searchAll($collection);
    }
}
