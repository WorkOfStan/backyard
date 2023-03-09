<?php

/**
 * Replacement for PHP functions that returns false or null instead of the strict type.
 * These function throw an \Exception instead.
 *
 * Usage:
 * put such line into a file declaration ...
 * use function WorkOfStan\Backyard\ThrowableFunctions\preg_replace;
 * ... and preg_replace will refer to this Throwable function, while
 * \preg_replace will refer to the PHP built-in function
 */

namespace WorkOfStan\Backyard\ThrowableFunctions;

use Exception;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

/**
 * Return the argument unless it is `null`.
 *
 * @param mixed $result
 * @return mixed
 * @throws Exception
 */
function throwOnNull($result)
{
    if (is_null($result)) {
        //throw new Exception('error (null) ' . debug_backtrace()[1]['function']); // PHP5.3 throws syntax error
        throw new Exception('error (null)'); // TODO use debug_backtrace once PHP5.3 backward compatibility not required
    }
    return $result;
}

/**
 *
 * @param string|string[] $pattern
 * @param string|string[] $replacement
 * @param string|string[] $subject
 * @param int $limit
 * @param int $count
 * @return string|string[]
 * @throws Exception
 */
function preg_replace($pattern, $replacement, $subject, $limit = -1, &$count = null)
{
    $result = \preg_replace($pattern, $replacement, $subject, $limit, $count);
    if (is_null($result)) {
        //throw new Exception('error (null) ' . debug_backtrace()[1]['function']); // PHP5.3 throws syntax error
        throw new Exception('error (null)'); // TODO use debug_backtrace once PHP5.3 backward compatibility not required
    }
    return $result;
}

/**
 * $subject is expected to be string, so the function returns string
 *
 * @param string|string[] $pattern
 * @param string|string[] $replacement
 * @param string $subject
 * @param int $limit
 * @param int $count
 * @return string
 * @throws Exception
 * @throws InvalidArgumentException
 */
function preg_replaceString($pattern, $replacement, $subject, $limit = -1, &$count = null)
{
    $result = throwOnNull(\preg_replace($pattern, $replacement, $subject, $limit, $count));
    Assert::string($result);
    return $result;
}
