<?php

/*
 * Florian Sager, 06.08.2008, sager@agitos.de, http://www.agitos.de
 *
 * Auto-Generate PHP array tree that contains all TLDs from the URL (see below);
 * The output has to be copied to reputation-libs/effectiveTLDs.inc.php
 *
 *
 */

header('Content-Type: text/html; charset=utf-8');

DEFINE('URL', 'http://mxr.mozilla.org/mozilla-central/source/netwerk/dns/effective_tld_names.dat?raw=1');

$format = "php";
if ($_SERVER['argc']>1) {
	if ($_SERVER['argv'][1] == "perl") {
		$format = "perl";
	} elseif ($_SERVER['argv'][1] == "c") {
		$format = "c";
	}
}

/*
 * Does $search start with $startstring?
 */
function startsWith($search, $startstring): bool {
	return (substr($search, 0, strlen($startstring))==$startstring);
}

/*
 * Does $search end with $endstring?
 */
function endsWith($search, $endstring): bool {
	return (substr($search, -strlen($endstring))==$endstring);
}


function buildSubdomain(&$node, $tldParts): void {

	$dom = trim(array_pop($tldParts));

	$isNotDomain = FALSE;
	if (startsWith($dom, "!")) {
		$dom = substr($dom, 1);
		$isNotDomain = TRUE;
	}

	if (!array_key_exists($dom, $node)) {
		$node[$dom] = $isNotDomain ? array("!" => "") : array();
	}

	if (!$isNotDomain && count($tldParts)>0) {
		buildSubdomain($node[$dom], $tldParts);
	}
}

/**
 * @return void
 */
function printNode($key, $valueTree, $isAssignment = false) {

	global $format;

	if ($isAssignment) {
		if ($format == "perl") {
			echo "$key = {";
		} else {
			echo "$key = array(";
		}
	} elseif (strcmp($key, "!")==0) {
		if ($format == "perl") {
				echo "'!' => {}";
			} else {
				echo "'!' => ''";
			}
		return;
	} elseif ($format == "perl") {
		echo "'$key' => {";
	} else {
				echo "'$key' => array(";
			}

	$keys = array_keys($valueTree);
	$keysCount = count($keys);

	for ($i=0; $i<$keysCount; $i++) {

		$key = $keys[$i];

		printNode($key, $valueTree[$key]);

		if ($i+1 != count($valueTree)) {
			echo ",\n";
		} else {
			echo "\n";
		}
	}

	if ($format == "perl") {
		echo '}';
	} else {
		echo ')';
	}
}

// sample: root(3:ac(5:com,edu,gov,net,ad(3:nom,co!,*)),de,com)

function printNode_C($key, $valueTree): void {

	echo "$key";

	$keys = array_keys($valueTree);

	if (count($keys)>0) {

		if (strcmp($keys['!'], "!")==0) { // @phpstan-ignore-line
			echo "!";
		} else {

			echo "(".count($keys).":";

			foreach ($keys as $i => $key) {
				$key = $key;
				// if (count($valueTree[$key])>0) {
				printNode_C($key, $valueTree[$key]);
				// }
				if ($i+1 != count($valueTree)) {
					echo ",";
				}
			}

			echo ')';
		}
	}
}

// --- main ---

error_reporting(E_ERROR);

$tldTree = array();
$list = file_get_contents(URL);
// $list = "bg\na.bg\n0.bg\n!c.bg\n";
$lines = explode("\n", $list);
$licence = TRUE;

if ($format == "php") echo "<?php\n";

foreach ($lines as $line) {
	$line = trim($line);
	if ($line == "") {
		if ($licence) {
			$licence = FALSE;
			echo "\n";
		}
		continue;
	}
	if (startsWith($line, "//")) {
		if ($licence) {
			if ($format == "perl") {
				echo "# ".substr($line, 2)."\n";
			} else {
				echo $line."\n";
			}
		}
		continue;
	}

	// this must be a TLD
	$tldParts = preg_split('/\./', $line);
	buildSubdomain($tldTree, $tldParts);
}

// print_r($tldTree);

/*
$tldTree = array(
	'de' => array(),		// test.agitos.de --> agitos.de
	'uk' => array(
		'co' => array(),	// test.agitos.co.uk --> agitos.co.uk
		'xy' => array('!'),	// test.agitos.xy.uk --> xy.uk
		'*' => array()		// test.agitos.ab.uk --> agitos.ab.uk
	)
);
*/

if ($format == "c") {

	echo "static const char tldString[] = \"";
	printNode_C("root", $tldTree);
	echo "\";\n\n";

} else {

	if ($format == "perl") {
		print "package effectiveTLDs;\n\n";
	}
	printNode("\$tldTree", $tldTree, TRUE);
	echo ";\n";
	if ($format == "php") echo '?>' . "\n";

}

?>
