<?php

namespace WorkOfStan\Backyard;

use Seablast\Logger\LoggerTime;

class BackyardTime extends LoggerTime
{
}

/**
 * Random seed initiation for mt_rand()
 */
/**
 * // Note: As of PHP 4.2.0, there is no need to seed the random number generator with srand() or mt_srand()
 * as this is now done automatically.
 * // www.su.cz má PHP 4.1.2 so: seed with microseconds
  function make_seed() {
  list($usec, $sec) = explode(' ', microtime());
  return (float) $sec + ((float) $usec * 100000);
  }
  mt_srand(make_seed());
 */
