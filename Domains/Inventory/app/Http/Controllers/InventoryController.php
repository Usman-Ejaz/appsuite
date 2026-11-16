<?php

namespace Domains\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Domains\Core\Requests\GetCollectionRequest;
use Domains\Core\Requests\GetResourceRequest;
use Domains\Inventory\Repsoitories\InventoryControllerRepository;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /**
     * Create the controller instance.
     */
    public function __construct(protected InventoryControllerRepository $repo)
    {
        //
    }

    /**
     * Display a listing of the resource.
     */
    public function index(GetCollectionRequest $request)
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     */
    public function get(GetResourceRequest $request, $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
