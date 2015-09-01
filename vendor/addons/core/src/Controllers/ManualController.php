<?php
namespace Addons\Core\Controllers;

use Addons\Core\Controllers\Controller;
use Illuminate\Http\Request;

use Addons\Core\Models\Manual;
class ManualController extends Controller
{
	public function index()
	{
		return $this->view('manual.index');
	}

	public function show(Request $request, $id)
	{
		$this->_data = Manual::findOrFail($id);
		return $this->view('manual.edit');
	}

	public function store(Request $request, $id)
	{

	}

	public function edit(Request $request, $id)
	{
		$this->_data = Manual::findOrFail($id);
		return $this->view('manual.edit');
	}

	public function update(Request $request, $id)
	{

	}

	
}