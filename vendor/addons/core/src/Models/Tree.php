<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;
class Tree extends Model {

	//不能批量赋值
	public $auto_cache = false;
	public $fire_caches = [];


	protected $guarded = ['id'];

	public $parentKey = 'pid'; //MySQL需加索引
	public $orderKey = 'order'; //MySQL需加索引
	public $pathKey = 'path'; //MySQL需加索引
	public $levelKey = 'level'; //MySQL需加索引

	public function children()
	{
		return $this->hasMany(get_class($this), 'id', 'pid');
	}

	/**
	 * 获取指定ID的Node
	 *
	 * @param  integer $cid 输入ID
	 * @return mixed      输出查询结果
	 */
	public function getNode($id, $columns = ['*'])
	{

		return empty($id) new static([
				$this->getKeyName() => NULL,
				$this->parentKey => 0,
				$this->pathKey => '/0/';
				$this->orderKey => 0;
				$this->levelKey => 0;
			]) : static::find($id, $columns);;

		return 
	}
	/**
	 * 获取所匹配的根节点Node
	 * 
	 * @return mixed      得到root
	 */
	public function getRoot($columns = ['*'])
	{
		$node = $this;
		if (!empty($this->pathKey))
		{
			while(!empty($node[$this->parentKey]))
				$node = $this->getNode($node[$this->parentKey], $columns);
			return $node;
		} else {
			list(,,$rootid) = explode('/', $node[$this->pathKey]); // [/0/1/2/3/] => 1
			return $this->getNode($rootid, $columns);			
		}
	}

	/**
	 * 获取父级的Node
	 * 
	 * @return mixed      得到父级元素
	 */
	public function getParent($columns = ['*'])
	{
		return $this->getNode($this[$this->parentKey], $columns);
	}

	/**
	 * 获取所匹配的祖先Nodes
	 *
	 * @return array     返回祖先
	 */
	public function getParents($columns = ['*'])
	{
		$result = $this->newCollection();
		$node = $this;
		while(!empty($node[$this->parentKey]))
		{
			$node = $this->getNode($node[$this->parentKey], $columns);
			$result[] = $node;
		}
		return $result;
	}

	/**
	 * 获得子集，返回一个Collection
	 *
	 * @param boolean $with_children 返回的数组中是否需要以child
	 * @return array 返回数据
	 */
	public function getChildren($columns = ['*'])
	{
		$builder = static::where($this->parentKey, $this->id);
		!empty($this->orderKey) && $builder->orderBy($this->orderKey);
		return $builder->get();
	}
	/**
	 * 获得所有子(孙)集，返回一个Collection
	 *
	 * @return array 返回数据
	 */
	public function getData($columns = ['*'])
	{
		$node = $this;
		if (!empty($this->pathKey))
		{
			$builder = static::where($this->pathKey, 'LIKE', $node[$this->pathKey].'%');
			!empty($this->orderKey) && $builder->orderBy($this->orderKey);
			return $this->get($columns = ['*']);
		} else {
			$result = $this->newCollection();
			
			$children = $this->getChildren($columns);
			array_walk($children, function($v) use ($result, $columns){
				$result[] = $v;
				$result->merge($this->getData($columns));
			});
			return $result;
		}
	}
	/**
	 * 获得所有子(孙)集，返回一个tree数组
	 *
	 * @param integer $pid 传入父级iD
	 * @return array 返回数据
	 */
	public function getTree($withSelf, $columns = ['*'])
	{
		$nodes = $this->getData()->fetch($this->getKeyName());
		return $this->_data_to_tree($nodes, $this->getKey(), $withSelf);

	}

	/**
	 * 将上面的二维class数组生成tree，必须是设置with_id的二维数组
	 *
	 * @param mixed $items 通过getData获得的二维数组(with_id)
	 * @param integer $topid 提供此二维数组中的顶级节点topid
	 */
	protected function _data_to_tree($items, $topid = 0, $with_parent = TRUE)
	{
		foreach ($items as $item)
			$items[ ($item[$this->parentKey]) ][ 'childern' ][ ($item[$this->getKeyName()]) ] = &$items[ ($item[$this->primary_key]) ];
		if ($with_parent)
	 		return isset($items[ $topid ]) ? $items[ $topid ] : array();
	 	else
	 		return isset($items[ $topid ][ 'childern' ]) ? $items[ $topid ][ 'childern' ] : array();
	}


	public function newOrder()
	{
		$parentKey = $this->parentKey;
		$orderKey = $this->orderKey;
		if (empty($orderKey)) return null;

		$node = static::where($parentKey, $this->$parentKey)->orderBy($this->orderKey, 'DESC')->first();
		return empty($node) ? 1 : $node->$orderKey + 1;
	}

	public function moveToLast()
	{
		$orderKey = $this->orderKey;
		if (empty($orderKey)) return null;

		$this->$orderKey = $this->newOrder();
		return $this->save();
	}

	public function movePrev($id)
	{

	}

	public function moveNext($id)
	{

	}

	public function changeParent($pid)
	{

	}

	
}