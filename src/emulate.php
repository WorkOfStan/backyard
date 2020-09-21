<?php
// phpcs:ignoreFile
error_log(__FILE__ . ' is obsolete - consider its rewriting');
die('LIB2'); //security die
/**
 * Name: Emulate
 * Project: LIB/Part of Library In Backyard
 *
 * *
 * Purpose:
 * UI for Content-proxy for spoofing HTTP headers
 * May be called through HTTP API. Uses emulator.php in iFrame.
 *
 *
 * *
 * History //@TODO (update $VERSION['emulate'])
 * 2012-04-12, v.0.1 - vývoj
 * 2012-04-16, v.0.2 - custom Headers added
 * 2012-04-30, v.0.3 - default behaviour is to absolutize references, may be changed by parameter ORIGINAL;pipe as delimiter of custom headers
 * 2012-04-30, v.0.4 - enable force content-type
 * 2014-07-17, v.0.5 - added to backyard 2
 *
 * *
 * TODO
 * @TODO - doplnit příklad
 * @TODO - Umožnit jít přímo na URL
 * @TODO - custom headers nastavitelné jen v expert mode (tedy default hidden)
 * @TODO add possibility to rewrite relative links within code to absolute links
 * @TODO add possibility to copy the target URL into clipboard
 * @TODO User-agent from db
 * @TODO - odpoutat emulator do samostatného okna: $pageInstance->addToBody("<td><a href='#' onclick='window.open(\"http://www.alfa.gods.cz/lib/emulator.php?url=".  urlencode($value)."&useragent=".  urlencode($row['User-agent'])."\", \"emulatorwindow\",\"location=1,status=0,scrollbars=1, width=240,height=320\");'>$value</a></td>");
 *
 * @TODO - sladit naming
 * ****************************
 * 			1. Naming conventions
 * I try to produce long, self-explaining method names.
 * Comments formatted as Phpdoc, JSDoc
 *  I prefer to tag the variable type. I write rather entityA (array of entities) than simple entities. For an instance of song object, rather than song I name the variable songO.
  Some examples:
 * variable, method – camelCase
 * class name – UpperCamelCase
 * url – hyphened-text
 * file – underscored_text
 * constant – BIG_LETTERS
  1. Comments
 * Primary language of comments is English.
 * Deprecated or obsolete code blocks are commented with prefix of the letter “x”. I may add reason for making the code obsolete as in the following:
  //Xhe’s got id from the beginning: $_SESSION["id"] = User::$himself->getId();
 * *******************************
 */
//

/**
 * Load Scripts & init
 */
//require_once ("./functions.php"); //require the basic LIB library; all other LIB components to be included by require_once (__ROOT__."/lib/XXX.php");
require_once __DIR__ . '/backyard_system.php'; //@TODO - is there a way to use the local backyard settings? //@TODO - maybe in future put out of backyard
/* database *//*
  require_once ("tableName.php"); //configuration of database connection of that script
  require_once (__ROOT__."/lib/connectDB.php");
  include (__ROOT__."/lib/openDB.php"); //ale asi neni nutne, protoze dotazy do db fungovaly i bez toho...snad si to pamatovalo otevreni z functions.php, prestoze tam bylo i uzavreno
  mysql_query("SET CHARACTER SET utf8");//aby se správně zapisovalo UTF-8 //http://php.vrana.cz/mysql-4-1-kodovani.php
 */
my_error_log("Knihovny pripojeny", 6, 6);

//$hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
require_once __BACKYARDROOT__ . "/class_HTMLPage.php"; //* If $CONTENT_TYPE == 'text/html' , be sure to set style.css in the same folder and have /jq/jquery-1.6.2.min.js present
$pageInstance = new HTMLPage("Emulate", "text/html", 1, 0); //jquery yes, style.css no
//$pageInstance->addToBody("<b>RED info</b><br/>".PHP_EOL);
/**
 * End of Load Scripts & init
 */
/* Display by include
  $URLinfo = array (
  'URL' => 'http://dadastrip.cz/test/?emulator', //URL
  'USER-AGENT' => 'NokiaN97/test' //User-agent
  );
  include ('emulator.php');
  echo "$result";  //@TODO - doplnit syntaxi podle emulator.php
 */

/* Body: Form and iFrame */


$pageInstance->addToBody('<div id="wrap">' . PHP_EOL); //envelope for nice buttons from emulate-buttons-include.html
$pageInstance->addToBody("<b>Emulate this...</b><br/>" . PHP_EOL);
$currentURL = (isset($_GET['url']) ? urldecode($_GET['url']) : '');
$pageInstance->addToBody('        <form action="emulate.php" method="GET" name="EmulatorConfigurationForm" enctype="application/x-www-form-urlencoded"><br/>' . PHP_EOL);
$pageInstance->addToBody('            <a href="' . $currentURL . '" target="directURL" title="Go directly to the URL">URL</a>: <input type="url" name="url" size="200" value="' . ($currentURL) . '" /><br/>' . PHP_EOL);
$pageInstance->addToBody('            User-agent: <input type="text" name="useragent" size="200" value="' . ($currentUserAgent = (isset($_GET['useragent']) ? urldecode($_GET['useragent']) : '')) . '" /><br/>' . PHP_EOL); //@TODO - takto pošle žádný UA. ?doplnit aktuální a nějak to vyznačit, třeba šedým písmem
$pageInstance->addToBody('            Custom headers: <input type="text" name="custom" size="200" value="' . ($currentCustomHeaders = (isset($_GET['custom']) ? urldecode($_GET['custom']) : '')) . '" /> (E.g. x-red-ip: 1.1.1.1|x-any: some .. more headers must be delimited by pipe without trailing spaces)<br/>' . PHP_EOL); //@TODO validation
$pageInstance->addToBody('            Width of display: <input type="number" name="width" value="' . ($currentWidth = (isset($_GET['width']) ? $_GET['width'] : 240)) . '" /><br/>' . PHP_EOL); // @TODO get value from cookie
$pageInstance->addToBody('            Height of display: <input type="number" name="height" value="' . ($currentHeight = (isset($_GET['height']) ? $_GET['height'] : 320)) . '" /><br/>' . PHP_EOL); // @TODO get value from cookie
$pageInstance->addToBody('            Display original mark-up [0/1]: <input type="number" name="original" value="' . ($currentOriginalMarkup = (isset($_GET['original']) ? $_GET['original'] : 0)) . '" /><br/>' . PHP_EOL); // @TODO get value from cookie // @TODO - make as a check button
$pageInstance->addToBody('            Force content-type: <input type="text" name="forcect" size="200" value="' . ($currentForceContentType = (isset($_GET['forcect']) ? urldecode($_GET['forcect']) : '')) . '" /> (E.g. text/plain)<br/>' . PHP_EOL); //@TODO .. dát i na výběr
$pageInstance->addToBody('            <input type="submit" name="submit" value="Refresh iframe" class="button"/><br/>' . PHP_EOL); // @TODO default value 'Refresh iframe' to be changed to 'Emulate!' only after form change
$pageInstance->addToBody('        </form><br/>' . PHP_EOL);
if ($currentURL != "") {
    my_error_log("targetSrc=" . ($targetSrc = 'emulator.php?url=' .
        urlencode($currentURL) . (($currentOriginalMarkup != '') ? '&original=' . urlencode($currentOriginalMarkup) : '') .
        (($currentForceContentType != '') ? '&forcect=' . urlencode($currentForceContentType) : '') .
        (($currentUserAgent != '') ? '&useragent=' . urlencode($currentUserAgent) : '') .
        (($currentCustomHeaders != '') ? ('&custom=' . urlencode($currentCustomHeaders)) : (''))), 3, 16);
    $pageInstance->addToBody('<iframe height="' . $currentHeight . '" width="' . $currentWidth . '" src="' . $targetSrc . '" ></iframe><br/>' . PHP_EOL);
}
$pageInstance->addToBody('<br/>Hint:<br/>*Copy the URL to remember the emulation.' . PHP_EOL);
$pageInstance->addToBody('<br/><br/><a href="mailto:rejthar@gods.cz?Subject=Emulate+feedback" class="button">Send feedback</a><br/>' . PHP_EOL);
$pageInstance->addToBody('</div>' . PHP_EOL);


//$pageInstance->addToBody(file_get_contents('emulate-buttons-include.html').PHP_EOL);
// **** END
/* HTML output management */
$pageInstance->startPage();
$pageInstance->endPage();
my_error_log("HTML vystup ukoncen", 6, 6);
/**/
/* database *//*
  my_error_log("DB uzavrit", 0, 6);
  include (__ROOT__."/lib/closeDB.php"); //uzavření přístupu do db, dále již žádné SQL requesty nebudou ani v rámci funkcí
  my_error_log("DB zavrena", 6, 6);
 */
my_error_log("End of page generating", 6, 6); //Zápis rychlosti vygenerování stránky do logu
