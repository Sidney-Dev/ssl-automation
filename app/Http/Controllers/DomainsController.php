<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDomainsRequest;
use App\Http\Requests\UpdateDomainsRequest;
use App\Models\Domains;

class DomainsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreDomainsRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDomainsRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Domains  $domains
     * @return \Illuminate\Http\Response
     */
    public function show(Domains $domains)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Domains  $domains
     * @return \Illuminate\Http\Response
     */
    public function edit(Domains $domains)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDomainsRequest  $request
     * @param  \App\Models\Domains  $domains
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDomainsRequest $request, Domains $domains)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Domains  $domains
     * @return \Illuminate\Http\Response
     */
    public function destroy(Domains $domains)
    {
        //
    }
}
