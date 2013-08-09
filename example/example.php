<?php

use Nel\Misc\BnpFile;
use Ryzom\Sheets\PackedSheetsLoader;
use Ryzom\Translation\Loader\UxtLoader;
use Ryzom\Translation\Loader\WordsLoader;
use Ryzom\Translation\StringsManager;

require __DIR__.'/../vendor/autoload.php';

$sm = new StringsManager();
$sm->register(new UxtLoader());
$sm->register(new WordsLoader());

$leveldesign = new BnpFile('/srv/home2/ryzom/data/gamedev.bnp');

// loading en.uxt file
$buffer = $leveldesign->readFile('en.uxt');
$sm->load('uxt', $buffer, 'en');
$strings = $sm->getStrings('uxt', 'en');

// all keys are in lowercase
$key = 'languagename';
$value = $strings[$key];
printf("%s = [%s]\n", $key, $value['name']);

// loading outpost_words_en.txt
$buffer = $leveldesign->readFile('outpost_words_en.txt');
$sm->load('outpost', $buffer, 'en');
$strings = $sm->getStrings('outpost', 'en');

$key = 'fyros_outpost_04';
$value = $strings[$key];
printf("%s\n  name = %s\n  description = %s\n", $key, $value['name'], $value['description']);

/*****************************************************************************/
echo "\n";
$psLoader = new PackedSheetsLoader('/srv/home2/ryzom/data');
printf("+ loading sitem.packed_sheets file...\n");
$sitem = $psLoader->load('sitem');

$sheetId = 5603886;
/** @var $item \Ryzom\Sheets\Client\ItemSheet */
$item = $sitem->get($sheetId);
printf(
	"+ %d: item type: %d, variant: %d, icons: (%s, %s), maleShape: %s\n",
	$sheetId, $item->ItemType, $item->MapVariant, $item->Icon[0], $item->Icon[1], $item->MaleShape
);
/*****************************************************************************/
$world = $psLoader->load('world');
//print_r($world);

/** @var \Ryzom\Sheets\ContinentLandmarks $lmconts */
$lmconts = $psLoader->load('lmconts');
/** @var \Ryzom\Sheets\Client\CContinent[] $continents */
$continents = $lmconts->get('continents');
foreach ($continents as $key => $cont) {
	echo "+ [{$cont->Name}]\n";
	foreach ($cont->ContLandMarks as $lm) {
		echo "  > {{$lm->TitleText}} {{$lm->Pos->X}, {$lm->Pos->Y}}\n";
	}
}

/*****************************************************************************/
$count = count($leveldesign->getFileNames());
echo "Iterating bnp... total files: $count, display first 10\n";
$c = 0;
foreach ($leveldesign as $filename => $filecontent) {
	printf("[%d] %s: size: %d\n", ++$c, $filename, strlen($filecontent));
	if ($c > 11) {
		break;
	}
}

