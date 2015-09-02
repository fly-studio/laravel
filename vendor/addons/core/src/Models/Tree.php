<?php
namespace Addons\Core\Models;

use Addons\Core\Models\Model;
use Addons\Core\Models\TreeTrait;
use DB;
class Tree extends Model {
	use TreeTrait;
	//不能批量赋值
	public $auto_cache = false;
	public $fire_caches = [];


	protected $guarded = ['id'];

	public $parentKey = 'pid'; //必要字段                MySQL需加索引
	public $orderKey = 'order'; //无需此字段请设置NULL   MySQL需加索引
	public $pathKey = 'path'; //无需此字段请设置NULL     MySQL需加索引
	public $levelKey = 'level'; //无需此字段请设置NULL   MySQL需加索引

	//relation
	public function children()
	{
		return $this->hasMany(get_class($this), $this->getParentKeyName(), $this->getKeyName());
	}

	public function parent()
	{
		return $this->hasOne(get_class($this), $this->getKeyName(), $this->getParentKeyName());
	}

	/**
	 * 获取指定ID的Node
	 *
	 * @param  integer $cid 输入ID
	 * @return mixed      输出查询结果
	 */
	public function getNode($id, $columns = ['*'])
	{

		return empty($id) ? new static([
				$this->getKeyName() => NULL,
				$this->parentKey => 0,
				$this->pathKey => '/0/',
				$this->orderKey => 0,
				$this->levelKey => 0,
			]) : static::find($id, $columns);
	}

	/**
	 * 获取所匹配的根节点Node
	 * 
	 * @return mixed      得到root
	 */
	public function getRoot($columns = ['*'])
	{
		$node = $this;
		if (empty($this->pathKey))
		{
			$columns = $this->formatColumns($columns);
			while(!empty($node->getParentKey()))
				$node = $this->getNode($node->getParentKey(), $columns);
			return $node;
		} else {
			list(,,$rootid) = explode('/', $node->getPathKey()); // [/0/1/2/3/] => 1
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
		return $this->getNode($this->getParentKey(), $columns);
	}

	/**
	 * 获取所匹配的祖先Nodes
	 *
	 * @return array     返回祖先
	 */
	public function getParents($columns = ['*'])
	{
		$columns = $this->formatColumns($columns);
		$result = $this->newCollection();
		$node = $this;
		while(!empty($node->getParentKey()))
		{
			$node = $this->getNode($node->getParentKey(), $columns);
			$result[] = $node;
		}
		return $result;
	}

	/**
	 * 获得子集，返回一个Collection
	 *
	 * @return array 返回数据
	 */
	public function getChildren($columns = ['*'])
	{
		$columns = $this->formatColumns($columns);
		$builder = static::where($this->parentKey, $this->id);
		!empty($this->orderKey) && $builder->orderBy($this->orderKey);
		return $builder->get();
	}

	/**
	 * 获得所有子(孙)集，返回一个Collection
	 *
	 * @return array 返回数据
	 */
	public function getDescendant($columns = ['*'])
	{
		$columns = $this->formatColumns($columns);
		$node = $this;
		if (!empty($this->pathKey)) //使用Path搜索出来的节点，无法通过order进行良好的排序
		{
			$builder = static::where($this->getPathKeyName(), 'LIKE', $node->getPathKey().'%');
			!empty($this->orderKey) && $builder->orderBy($this->getOrderKeyName());
			!empty($this->pathKey) && $builder->orderBy($this->getPathKeyName());
			return $builder->get($columns);
		} else {
			$result = $this->newCollection();
			
			$children = $this->getChildren($columns);
			array_walk($children, function($v) use ($result, $columns){
				$result[] = $v;
				$result->merge($this->getDescendant($columns));
			});
			return $result;
		}
	}
	/**
	 * 获得所有子(孙)集，返回一个tree数组
	 *
	 * @return array 返回数据
	 */
	public function getTree($columns = ['*'])
	{
		$nodes = $this->getDescendant($columns)->keyBy($this->getKeyName())->toArray();
		return $this->_data_to_tree($nodes, $this->getParentKey());

	}

	/**
	 * 将上面的二维class数组生成tree，必须是设置with_id的二维数组
	 *
	 * @param mixed $items 通过getData获得的二维数组(with_id)
	 * @param integer $topid 提供此二维数组中的顶级节点topid
	 */
	protected function _data_to_tree($items, $topid = 0)
	{
		foreach ($items as $item){
			$items[ ($item[$this->getParentKeyName()]) ][ 'children' ][ ($item[$this->getKeyName()]) ] = &$items[ ($item[$this->getKeyName()]) ];
		}

	 	return isset($items[ $topid ][ 'children' ]) ? $items[ $topid ][ 'children' ] : array();
	}

	private function formatColumns($columns)
	{
		if (in_array('*', $columns)) return $columns;
		!in_array($this->getKeyName(), $columns) && $columns[] = $this->getKeyName();
		!in_array($this->parentKey, $columns) && $columns[] = $this->parentKey;

		return $columns;
	}


	public function newOrder()
	{
		$parentKey = $this->parentKey;
		$orderKey = $this->orderKey;
		if (empty($orderKey)) return null;

		$node = static::where($parentKey, $this->$parentKey)->orderBy($this->orderKey, 'DESC')->first();
		return empty($node) ? 1 : intval($node->$orderKey) + 1;
	}

	public function moveToLast()
	{
		$orderKey = $this->orderKey;
		if (empty($orderKey)) return null;

		$this->$orderKey = $this->newOrder();
		return $this->save();
	}

	public function movePrev($target_id)
	{
		return $this->move($target_id, 'prev');
	}

	public function moveNext($target_id)
	{
		return $this->move($target_id, 'next');
	}

	public function moveInner($target_id)
	{
		return $this->move($target_id, 'inner');
	}
	/**
	 * 改变父级，本函数不可再任何情况下调用
	 * $tree->update(['pid' => 'xxx']) 会自动调用本函数
	 * 
	 * @return [type]          [description]
	 */
	protected function changeParent()
	{
		$newParent = $this->getNode($this->getParentKey());
		if(empty($newParent) || $this->getParentKey() == $this->getOriginal($this->getParentKeyName())) return NULL;

		if (!empty($this->pathKey))
		{
			$newPath = $newParent->getPathKey() . $this->getKey() . '/';
			static::where($this->pathKey, 'LIKE', '%'.$this->getPathKey().'%')->update([$this->getPathKeyName() => DB::raw('REPLACE(`'.$this->getPathKeyName().'`, \''.$this->getPathKey().'\', \''.$newPath.'\')')]);
		}

		if (!empty($this->levelKey))
		{
			$newPath = intval($this->{$this->levelKey}) - intval($newParent->{$this->levelKey}) - 1;
			$ids = $this->getDescendant([])->add($this)->fetch($this->getKeyName()); //get id pid
			static::where($this->getKeyName(), 'IN', $ids)->decrement($this->levelKey, $delta);
		}
		

		return $this;
	}

	/**
	 * 移动节点
	 * @param  integer $target_id   目标CID
	 * @param  string $move_type     [inner, prev, next]分别表示：子集、前一位、后一位
	 * @return boolean
	 */
	public function move($target_id, $move_type)
	{
		$targetNode = $this->getNode($target_id);
		if (empty($target_id) || empty($targetNode)) return NULL;

		if ($move_type == 'inner') //成为别人子集，则直接调用,放入子集
		{
			return $this->update([$this->getParentKeyName() => $target_id]); //自动调取changeParent
		}
		//父级不相同
		if ($targetNode->getParentKey() != $this->getParentKey())
			$this->update([$this->getParentKeyName() => $targetNode->getParentKey()]); //自动调取changeParent

		if (!empty($this->orderKey))
		{
			$order = intval($targetNode->getOrderKey()) + ($move_type == 'prev' ? 0 : 1);
			//更新同父级下所有的顺序
			static::where($this->getParentKeyName(), $targetNode->getParentKey())->where($this->getOrderKeyName(), '>=', $order)->increment($this->getOrderKeyName());
			$this->update([$this->getOrderKeyName() => $order]); //更新自己的顺序
		}
	}

	public function getParentKey()
	{
		return $this->{$this->parentKey};
	}

	public function getParentKeyName()
	{
		return $this->parentKey;
	}

	public function getOrderKey()
	{
		return $this->{$this->orderKey};
	}

	public function getOrderKeyName()
	{
		return $this->orderKey;
	}

	public function getPathKey()
	{
		return $this->{$this->pathKey};
	}

	public function getPathKeyName()
	{
		return $this->pathKey;
	}

	public function getLevelKey()
	{
		return $this->{$this->levelKey};
	}

	public function getLevelKeyName()
	{
		return $this->levelKey;
	}

	
}
