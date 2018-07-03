<?php

namespace Addons\Server\Routing;

use LogicException;
use DomainException;
use InvalidArgumentException;
use Addons\Server\Routing\Route;
use Addons\Server\Routing\CompiledRoute;

class RouteCompiler {

	const REGEX_DELIMITER = '#';

	/**
	 * This string defines the characters that are automatically considered separators in front of
	 * optional placeholders (with default and no static text following). Such a single separator
	 * can be left out together with the optional placeholder from matching and generating URLs.
	 */
	const SEPARATORS = '/,;.:-_~+*=@|';

	/**
	 * The maximum supported length of a PCRE subpattern name
	 * http://pcre.org/current/doc/html/pcre2pattern.html#SEC16.
	 *
	 * @internal
	 */
	const VARIABLE_MAXIMUM_LENGTH = 32;

	protected $pattern = '';
	protected $defaults = [];
	protected $requirements = [];
	protected $options = [];
	protected $condition = '';
	/**
	 * @var null|CompiledRoute
	 */
	private $compiled;

	public function __construct(Route $route)
	{
		// 特征值
		$pattern = $route->getPattern();
		// 初步筛选可选参数
		$optionals = $this->getOptionalParameters($pattern);
		// 将所有参数替换为必要参数
		$pattern = preg_replace('/\{(\w+?)\?\}/', '{$1}', $pattern);

		$this->setPattern($pattern);
		$this->addDefaults($optionals);
		$this->addRequirements($route->getWheres());
	}

	public function setPattern(string $pattern)
	{
		if (false !== strpbrk($pattern, '?<')) {
			$pattern = preg_replace_callback('#\{(\w++)(<.*?>)?(\?[^\}]*+)?\}#', function ($m) {
				if (isset($m[3][0])) {
					$this->setDefault($m[1], '?' !== $m[3] ? substr($m[3], 1) : null);
				}
				if (isset($m[2][0])) {
					$this->setRequirement($m[1], substr($m[2], 1, -1));
				}

				return '{'.$m[1].'}';
			}, $pattern);
		}

		$this->pattern = $pattern;
		$this->compiled = null;

		return $this;
	}

	public function getPattern()
	{
		return $this->pattern;
	}

	/**
	 * Returns the defaults.
	 *
	 * @return array The defaults
	 */
	public function getDefaults()
	{
		return $this->defaults;
	}

	/**
	 * Sets the defaults.
	 *
	 * This method implements a fluent interface.
	 *
	 * @param array $defaults The defaults
	 *
	 * @return $this
	 */
	public function setDefaults(array $defaults)
	{
		$this->defaults = [];

		return $this->addDefaults($defaults);
	}

	/**
	 * Gets a default value.
	 *
	 * @param string $name A variable name
	 *
	 * @return mixed The default value or null when not given
	 */
	public function getDefault($name)
	{
		return isset($this->defaults[$name]) ? $this->defaults[$name] : null;
	}

	/**
	 * Checks if a default value is set for the given variable.
	 *
	 * @param string $name A variable name
	 *
	 * @return bool true if the default value is set, false otherwise
	 */
	public function hasDefault($name)
	{
		return array_key_exists($name, $this->defaults);
	}

	/**
	 * Sets a default value.
	 *
	 * @param string $name    A variable name
	 * @param mixed  $default The default value
	 *
	 * @return $this
	 */
	public function setDefault($name, $default)
	{
		$this->defaults[$name] = $default;
		$this->compiled = null;

		return $this;
	}

	/**
	 * Returns the requirements.
	 *
	 * @return array The requirements
	 */
	public function getRequirements()
	{
		return $this->requirements;
	}

	/**
	 * Sets the requirements.
	 *
	 * This method implements a fluent interface.
	 *
	 * @param array $requirements The requirements
	 *
	 * @return $this
	 */
	public function setRequirements(array $requirements)
	{
		$this->requirements = [];

		return $this->addRequirements($requirements);
	}

	/**
	 * Adds requirements.
	 *
	 * This method implements a fluent interface.
	 *
	 * @param array $requirements The requirements
	 *
	 * @return $this
	 */
	public function addRequirements(array $requirements)
	{
		foreach ($requirements as $key => $regex) {
			$this->requirements[$key] = $this->sanitizeRequirement($key, $regex);
		}
		$this->compiled = null;

		return $this;
	}

	/**
	 * Returns the requirement for the given key.
	 *
	 * @param string $key The key
	 *
	 * @return string|null The regex or null when not given
	 */
	public function getRequirement($key)
	{
		return isset($this->requirements[$key]) ? $this->requirements[$key] : null;
	}

	/**
	 * Checks if a requirement is set for the given key.
	 *
	 * @param string $key A variable name
	 *
	 * @return bool true if a requirement is specified, false otherwise
	 */
	public function hasRequirement($key)
	{
		return array_key_exists($key, $this->requirements);
	}

	/**
	 * Sets a requirement for the given key.
	 *
	 * @param string $key   The key
	 * @param string $regex The regex
	 *
	 * @return $this
	 */
	public function setRequirement($key, $regex)
	{
		$this->requirements[$key] = $this->sanitizeRequirement($key, $regex);
		$this->compiled = null;

		return $this;
	}

	/**
	 * Returns the condition.
	 *
	 * @return string The condition
	 */
	public function getCondition()
	{
		return $this->condition;
	}

	/**
	 * Sets the condition.
	 *
	 * This method implements a fluent interface.
	 *
	 * @param string $condition The condition
	 *
	 * @return $this
	 */
	public function setCondition($condition)
	{
		$this->condition = (string) $condition;
		$this->compiled = null;

		return $this;
	}

	/**
	 * Adds defaults.
	 *
	 * This method implements a fluent interface.
	 *
	 * @param array $defaults The defaults
	 *
	 * @return $this
	 */
	public function addDefaults(array $defaults)
	{
		foreach ($defaults as $name => $default) {
			$this->defaults[$name] = $default;
		}
		$this->compiled = null;

		return $this;
	}


	/**
	 * {@inheritdoc}
	 *
	 * @throws InvalidArgumentException if a path variable is named _fragment
	 * @throws LogicException           if a variable is referenced more than once
	 * @throws DomainException          if a variable name starts with a digit or if it is too long to be successfully used as
	 *                                   a PCRE subpattern
	 */
	public function compile(): CompiledRoute
	{
		$variables = [];

		$result = $this->compilePattern($this->getPattern());

		$staticPrefix = $result['staticPrefix'];

		$variables = $result['variables'];

		foreach ($variables as $param) {
			if ('_fragment' === $param) {
				throw new InvalidArgumentException(sprintf('Route pattern "%s" cannot contain "_fragment" as a path parameter.', $this->getPattern()));
			}
		}

		$tokens = $result['tokens'];
		$regex = $result['regex'];

		return new CompiledRoute(
			$staticPrefix,
			$regex,
			$tokens,
			array_unique($variables)
		);
	}

	/**
	 * Get the optional parameters for the route.
	 *
	 * @return array
	 */
	protected function getOptionalParameters(string $pattern)
	{
		preg_match_all('/\{(\w+?)\?\}/', $pattern, $matches);

		return isset($matches[1]) ? array_fill_keys($matches[1], null) : [];
	}

	private function compilePattern($pattern)
	{
		$tokens = [];
		$variables = [];
		$matches = [];
		$pos = 0;
		$defaultSeparator = '#';
		$useUtf8 = preg_match('//u', $pattern);
		$needsUtf8 = true;

		// Match all variables enclosed in "{}" and iterate over them. But we only want to match the innermost variable
		// in case of nested "{}", e.g. {foo{bar}}. This in ensured because \w does not match "{" or "}" itself.
		preg_match_all('#\{\w+\}#', $pattern, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
		foreach ($matches as $match) {
			$varName = substr($match[0][0], 1, -1);
			// get all static text preceding the current variable
			$precedingText = substr($pattern, $pos, $match[0][1] - $pos); // { 前面的字符串
			$pos = $match[0][1] + strlen($match[0][0]); // } 后面的POS

			// 最后一位字符
			if (!strlen($precedingText)) {
				$precedingChar = '';
			} elseif ($useUtf8) {
				preg_match('/.$/u', $precedingText, $precedingChar);
				$precedingChar = $precedingChar[0];
			} else {
				$precedingChar = substr($precedingText, -1);
			}
			$isSeparator = '' !== $precedingChar && false !== strpos(static::SEPARATORS, $precedingChar);

			// A PCRE subpattern name must start with a non-digit. Also a PHP variable cannot start with a digit so the
			// variable would not be usable as a Controller action argument.
			if (preg_match('/^\d/', $varName)) { //数字开头
				throw new DomainException(sprintf('Variable name "%s" cannot start with a digit in route pattern "%s". Please use a different name.', $varName, $pattern));
			}
			if (in_array($varName, $variables)) { //同名param
				throw new LogicException(sprintf('Route pattern "%s" cannot reference variable name "%s" more than once.', $pattern, $varName));
			}

			if (strlen($varName) > static::VARIABLE_MAXIMUM_LENGTH) { //限制长度
				throw new DomainException(sprintf('Variable name "%s" cannot be longer than %s characters in route pattern "%s". Please use a shorter name.', $varName, static::VARIABLE_MAXIMUM_LENGTH, $pattern));
			}

			if ($isSeparator && $precedingText !== $precedingChar) {
				$tokens[] = array('text', substr($precedingText, 0, -strlen($precedingChar))); //去掉最后一位分隔符
			} elseif (!$isSeparator && strlen($precedingText) > 0) {
				$tokens[] = array('text', $precedingText);
			}

			$regexp = $this->getRequirement($varName); // get where
			if (null === $regexp) {
				$followingPattern = (string) substr($pattern, $pos);
				// 比如 words{name} 这种形态会导致分析结果不可预料，所以在创建表达式的时候使用分割符来分离words和{name}，比如 words~{name}
				// Find the next static character after the variable that functions as a separator. By default, this separator and '/'
				// are disallowed for the variable. This default requirement makes sure that optional variables can be matched at all
				// and that the generating-matching-combination of URLs unambiguous, i.e. the params used for generating the URL are
				// the same that will be matched. Example: new Route('/{page}.{_format}', array('_format' => 'html'))
				// If {page} would also match the separating dot, {_format} would never match as {page} will eagerly consume everything.
				// Also even if {_format} was not optional the requirement prevents that {page} matches something that was originally
				// part of {_format} when generating the URL, e.g. _format = 'mobile.html'.
				$nextSeparator = $this->findNextSeparator($followingPattern, $useUtf8);
				$regexp = sprintf(
					'[^%s%s]+',
					preg_quote($defaultSeparator, static::REGEX_DELIMITER),
					$defaultSeparator !== $nextSeparator && '' !== $nextSeparator ? preg_quote($nextSeparator, static::REGEX_DELIMITER) : ''
				);
				if (('' !== $nextSeparator && !preg_match('#^\{\w+\}#', $followingPattern)) || '' === $followingPattern) {
					// When we have a separator, which is disallowed for the variable, we can optimize the regex with a possessive
					// quantifier. This prevents useless backtracking of PCRE and improves performance by 20% for matching those patterns.
					// Given the above example, there is no point in backtracking into {page} (that forbids the dot) when a dot must follow
					// after it. This optimization cannot be applied when the next char is no real separator or when the next variable is
					// directly adjacent, e.g. '/{x}{y}'.
					$regexp .= '+';
				}
			} else {
				if (!preg_match('//u', $regexp)) {
					$useUtf8 = false;
				} elseif (!$needsUtf8 && preg_match('/[\x80-\xFF]|(?<!\\\\)\\\\(?:\\\\\\\\)*+(?-i:X|[pP][\{CLMNPSZ]|x\{[A-Fa-f0-9]{3})/', $regexp)) {
					throw new LogicException(sprintf('Cannot use UTF-8 route requirements without setting the "utf8" option for variable "%s" in pattern "%s".', $varName, $pattern));
				}
				if (!$useUtf8 && $needsUtf8) {
					throw new LogicException(sprintf('Cannot mix UTF-8 requirement with non-UTF-8 charset for variable "%s" in pattern "%s".', $varName, $pattern));
				}
				$regexp = $this->transformCapturingGroupsToNonCapturings($regexp);
			}

			$tokens[] = array('variable', $isSeparator ? $precedingChar : '', $regexp, $varName);
			$variables[] = $varName;
		}

		if ($pos < strlen($pattern)) { // 没有到结尾，将尾部字符串添加
			$tokens[] = array('text', substr($pattern, $pos));
		}

		// find the first optional token
		$firstOptional = PHP_INT_MAX;
		for ($i = count($tokens) - 1; $i >= 0; --$i) {
			$token = $tokens[$i];
			if ('variable' === $token[0] && $this->hasDefault($token[3])) {
				$firstOptional = $i;
			} else {
				break;
			}
		}

		// compute the matching regexp
		$regexp = '';
		for ($i = 0, $nbToken = count($tokens); $i < $nbToken; ++$i) {
			$regexp .= $this->computeRegexp($tokens, $i, $firstOptional);
		}
		$regexp = static::REGEX_DELIMITER.'^'.$regexp.'$'.static::REGEX_DELIMITER.'sD';

		// enable Utf8 matching if really required
		if ($needsUtf8) {
			$regexp .= 'u';
			for ($i = 0, $nbToken = count($tokens); $i < $nbToken; ++$i) {
				if ('variable' === $tokens[$i][0]) {
					$tokens[$i][] = true;
				}
			}
		}

		return [
			'staticPrefix' => $this->determineStaticPrefix($tokens),
			'regex' => $regexp,
			'tokens' => array_reverse($tokens),
			'variables' => $variables,
		];
	}

	/**
	 * Determines the longest static prefix possible for a route.
	 */
	private function determineStaticPrefix(array $tokens): string
	{
		if ('text' !== $tokens[0][0]) {
			return ($this->hasDefault($tokens[0][3]) || '/' === $tokens[0][1]) ? '' : $tokens[0][1];
		}

		$prefix = $tokens[0][1];

		if (isset($tokens[1][1]) && '/' !== $tokens[1][1] && false === $this->hasDefault($tokens[1][3])) {
			$prefix .= $tokens[1][1];
		}

		return $prefix;
	}

	/**
	 * Returns the next static character in the Route pattern that will serve as a separator (or the empty string when none available).
	 */
	private function findNextSeparator(string $pattern, bool $useUtf8): string
	{
		if ('' == $pattern) {
			// return empty string if pattern is empty or false (false which can be returned by substr)
			return '';
		}
		// first remove all placeholders from the pattern so we can find the next real static character
		if ('' === $pattern = preg_replace('#\{\w+\}#', '', $pattern)) {
			return '';
		}
		if ($useUtf8) {
			preg_match('/^./u', $pattern, $pattern);
		}

		return false !== strpos(static::SEPARATORS, $pattern[0]) ? $pattern[0] : '';
	}

	/**
	 * Computes the regexp used to match a specific token. It can be static text or a subpattern.
	 *
	 * @param array $tokens        The route tokens
	 * @param int   $index         The index of the current token
	 * @param int   $firstOptional The index of the first optional token
	 *
	 * @return string The regexp pattern for a single token
	 */
	private function computeRegexp(array $tokens, int $index, int $firstOptional): string
	{
		$token = $tokens[$index];
		if ('text' === $token[0]) {
			// Text tokens
			return preg_quote($token[1], static::REGEX_DELIMITER);
		} else {
			// Variable tokens
			if (0 === $index && 0 === $firstOptional) {
				// When the only token is an optional variable token, the separator is required
				return sprintf('%s(?P<%s>%s)?', preg_quote($token[1], static::REGEX_DELIMITER), $token[3], $token[2]);
			} else {
				$regexp = sprintf('%s(?P<%s>%s)', preg_quote($token[1], static::REGEX_DELIMITER), $token[3], $token[2]);
				if ($index >= $firstOptional) {
					// Enclose each optional token in a subpattern to make it optional.
					// "?:" means it is non-capturing, i.e. the portion of the subject string that
					// matched the optional subpattern is not passed back.
					$regexp = "(?:$regexp";
					$nbTokens = count($tokens);
					if ($nbTokens - 1 == $index) {
						// Close the optional subpatterns
						$regexp .= str_repeat(')?', $nbTokens - $firstOptional - (0 === $firstOptional ? 1 : 0));
					}
				}

				return $regexp;
			}
		}
	}

	private function transformCapturingGroupsToNonCapturings(string $regexp): string
	{
		for ($i = 0; $i < \strlen($regexp); ++$i) {
			if ('\\' === $regexp[$i]) {
				++$i;
				continue;
			}
			if ('(' !== $regexp[$i] || !isset($regexp[$i + 2])) {
				continue;
			}
			if ('*' === $regexp[++$i] || '?' === $regexp[$i]) {
				++$i;
				continue;
			}
			$regexp = substr_replace($regexp, '?:', $i, 0);
			$i += 2;
		}

		return $regexp;
	}

	private function sanitizeRequirement($key, $regex)
	{
		if (!is_string($regex)) {
			throw new \InvalidArgumentException(sprintf('Routing requirement for "%s" must be a string.', $key));
		}

		if ('' !== $regex && '^' === $regex[0]) {
			$regex = (string) substr($regex, 1); // returns false for a single character
		}

		if ('$' === substr($regex, -1)) {
			$regex = substr($regex, 0, -1);
		}

		if ('' === $regex) {
			throw new \InvalidArgumentException(sprintf('Routing requirement for "%s" cannot be empty.', $key));
		}

		return $regex;
	}
}
