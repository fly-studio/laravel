<?php
namespace Addons\Core\Controllers;

use Addons\Core\Controllers\Controller;
use Illuminate\Http\Request;
use Addons\Core\Models\Manual;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Addons\Core\Validation\ValidatesRequests;
class ManualController extends Controller
{
	use DispatchesJobs, ValidatesRequests;
	public function index()
	{
		return $this->view('manual.index');
	}

	public function create(Request $request)
	{
		$keys = 'title,content,pid';
		$this->_data = [];
		$this->_tree = (new Manual)->getNode(0)->getDescendant(['title', 'level']);
		$this->_validates = $this->getScriptValidate('manual.store', $keys);
		return $this->view('manual.create');
	}

	public function show(Request $request, $id)
	{
		$this->_data = Manual::findOrFail($id);
		$this->_tree = (new Manual)->getNode(0)->getDescendant(['title', 'level']);
		return $this->view('manual.show');
	}

	public function store(Request $request)
	{
		$keys = 'title,content,pid';
		$data = $this->autoValidate($request, 'manual.store', $keys);

		$manual = Manual::create($data);
		return $this->success('', url('manual/' . $manual->getKey()));
	}

	public function edit(Request $request, $id)
	{
		$keys = 'title,content,pid';
		$this->_data = Manual::findOrFail($id);
		$this->_tree = (new Manual)->getNode(0)->getDescendant(['title', 'level']);
		$this->_validates = $this->getScriptValidate('manual.store', $keys);
		return $this->view('manual.edit');
	}

	public function update(Request $request, $id)
	{
		$manual = Manual::findOrFail($id);
		$keys = 'title,content,pid';
		$data = $this->autoValidate($request, 'manual.store', $keys);

		$manual->update($data);
		return $this->success('', url('manual/' . $manual->getKey()));
	}

	
}