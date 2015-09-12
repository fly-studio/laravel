<?php

namespace Addons\Core\Middleware;

use Closure;
use Addons\Core\Models\Wechat\Account;
class WechatAccount
{
	/**
	 * The \Addons\Core\Models\Wechat\Account.
	 *
	 * @var \Addons\Core\Models\Wechat\Account
	 */
	protected $account;

	/**
	 * Create a new filter instance.
	 *
	 * @param  \Addons\Core\Models\Wechat\Account  $auth
	 * @return void
	 */
	public function __construct(Account $account)
	{
		$this->account = $account;
	}

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		if (empty($this->account->getAccountID()))
		{
			if ($request->ajax()) {
				return response('Unauthorized.', 401);
			} else {
				return redirect()->guest('wechat/chosen');
			}
		}

		return $next($request);
	}
}