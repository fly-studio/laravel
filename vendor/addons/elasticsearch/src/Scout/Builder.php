<?php
namespace Addons\Elasticsearch\Scout;

use Laravel\Scout\Builder as BaseBuilder;
use Illuminate\Support\Collection;
use Closure;
class Builder extends BaseBuilder{

	public $_source = false;
    public $_count = false;

    private $whereCollection;

    /**
     * Create a new search builder instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $query
     * @param  Closure  $callback
     * @return void
     */
    public function __construct($model, $wheres = null, $callback = null)
    {
        $this->model = $model;
        is_null($wheres) && $wheres = new Collection();
        $wheres['bool'] = new Collection([
            'must' => new Collection(),
        ]);
        $this->wheres = $wheres;
        $this->whereCollection = $this->wheres['bool']['must'];
        $this->callback = $callback;
    }

	public function get($columns = ['*'])
	{
		$this->_source = $columns;
		return $this->engine()->get($this);
	}

	/**
     * Get the first result from the search.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function first($columns = ['*'])
    {
		$this->_source = $columns;
        return $this->get()->first();
    }

    /**
     * Get the count from the search.
     * it's easy way, with _count API of elastic 
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
	public function count()
	{
		return $this->engine()->count($this);
	}

	/**
     * Paginate the given query into a simple paginator.
     *
     * @param  int  $perPage
     * @param  boolean|array filter columns form _source
     * @param  string  $pageName
     * @param  int|null  $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
		$this->_source = $columns;
    	return parent::paginate($perPage, $pageName, $page);
    }

    /**
     * Add a constraint to the search query.
     *
     * @example where('name', 'admin')
     * @example where(['name', 'title'], 'admin')
     *
     * @param  string|array  $column
     * @param  mixed  $value
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($column instanceof Closure)
        {
            $this->whereCollection[] = new Collection();
            $wheres = $this->whereCollection->last();
            $new = new static($this->model, $wheres);
            call_user_func_array($column, [$new]);
        } else
            $this->parseWhere($column, $operator, $value);

        return $this;
    }


    private function parseWhere($column, $operator, $value)
    {
        if (is_null($value) && !is_null($operator)) { // operator, default = 
            $value = $operator;
            $operator = is_array($column) ? 'multi_match' : '=';
        }
        if ($column == '_all') {
            $this->whereCollection[] = [
                'match' => [
                    '_all' => [
                        'query' => $value,
                        'fuzziness' => 1,
                    ],
                ],
            ];
        }
        else if (is_array($column) && is_null($value) && is_null($operator)) //append data
        {
            if (is_assoc($column))
                $this->whereCollection->merge($columns);
            else
                $this->whereCollection[] = $columns;
        }
        else if (!is_null($value))
        {
            switch (strtolower($operator)) {
                case '=':
                case 'term':
                    $this->whereCollection[] = [
                        'term' => [
                            $column => $value,
                        ],
                    ];
                    break;
                case 'in':
                case 'terms':
                    $this->whereCollection[] = [
                        'terms' => [
                            $column => $value,
                        ],
                    ];
                    break;
                case 'like':
                case 'match':
                    $this->whereCollection[] = [
                        'match' => [
                            $column => $value,
                            'operator' => 'and',
                        ],
                    ];
                    break;
                case 'multi_match':
                    $this->whereCollection[] = [
                        'multi_match' => [
                            'fileds' => $column,
                            'query' => $value,
                        ],
                    ];
                    break;
                default:
                    break;
            }
        }
            
    }

}