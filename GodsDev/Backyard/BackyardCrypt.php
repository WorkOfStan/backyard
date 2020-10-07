<?php

namespace GodsDev\Backyard;

use Psr\Log\LoggerInterface;

class BackyardCrypt
{

    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger = null;

    /**
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Returns the custom length unique id; default is 10
     * Based on http://phpgoogle.blogspot.com/2007/08/four-ways-to-generate-unique-id-by-php.html
     *
     * @param int $randomIdLength
     * @return string
     */
    public function randomId($randomIdLength = 10)
    {
        //generate a random id encrypt it and store it in $rndId
        $rndId = crypt(uniqid((string) rand(), true), uniqid((string) rand(), true));

        //to remove any slashes that might have come
        $rndId = strip_tags(stripslashes($rndId));

        //Removing any . or / and reversing the string
        $rndId = strrev(str_replace("/", "", str_replace(".", "", $rndId)));

        if (strlen($rndId) < $randomIdLength) {
            $rndId = $rndId . $this->randomId($randomIdLength - strlen($rndId));
        }

        //finally I take the first 10 characters from the $rndId
        $rndId = substr($rndId, 0, $randomIdLength);
        $this->logger->log(5, "Random id is " . $rndId, array(16));
        return $rndId;
    }
}
