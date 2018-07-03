<?php

namespace Addons\Server\Routing;

/**
 * CompiledRoutes are returned by the RouteCompiler class.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class CompiledRoute implements \Serializable {

	private $variables;
    private $tokens;
    private $staticPrefix;
    private $regex;

    /**
     * @param string      $staticPrefix  The static prefix of the compiled route
     * @param string      $regex         The regular expression to use to match this route
     * @param array       $tokens        An array of tokens to use to generate URL for this route
     * @param array       $variables     An array of variables (variables defined in the path and in the host patterns)
     */
    public function __construct(?string $staticPrefix, string $regex, ?array $tokens, ?array $variables = [])
    {
        $this->staticPrefix = $staticPrefix;
        $this->regex = $regex;
        $this->tokens = $tokens;
        $this->variables = $variables;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize([
            'vars' => $this->variables,
            'path_prefix' => $this->staticPrefix,
            'path_regex' => $this->regex,
            'path_tokens' => $this->tokens,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized, ['allowed_classes' => false]);

        $this->variables = $data['vars'];
        $this->staticPrefix = $data['path_prefix'];
        $this->regex = $data['path_regex'];
        $this->tokens = $data['path_tokens'];
    }

    /**
     * Returns the static prefix.
     *
     * @return string The static prefix
     */
    public function getStaticPrefix()
    {
        return $this->staticPrefix;
    }

    /**
     * Returns the regex.
     *
     * @return string The regex
     */
    public function getRegex()
    {
        return $this->regex;
    }

    /**
     * Returns the tokens.
     *
     * @return array The tokens
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * Returns the variables.
     *
     * @return array The variables
     */
    public function getVariables()
    {
        return $this->variables;
    }

}
