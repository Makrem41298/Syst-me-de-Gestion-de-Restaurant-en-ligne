<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

interface ManagerTable
{
    public function index();
    public function show($id);
    public function store( Request $request);
    public function update(Request $request, $id);
    public function destroy($id);

}
