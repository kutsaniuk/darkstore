<?php

require_once __DIR__ . '/../MpdfException.php';

/*********************************************************************************
 * TTFontFile class                                                             *
 *                                                                              *
 * Version:  4.01		                                                        *
 * Date:     2014-10-25                                                         *
 * Author:   Ian Back <ianb@bpm1.com>                                           *
 * License:  LGPL                                                               *
 * Copyright (c) Ian Back, 2010                                                 *
 * This class is based on The ReportLab Open Source PDF library                 *
 * written in Python - http://www.reportlab.com/software/opensource/            *
 * together with ideas from the OpenOffice source code and others.              *
 * This header must be retained in any redistribution or                        *
 * modification of the file.                                                    *
 *                                                                              *
 ********************************************************************************/

// NOTE*** If you change the defined constants below, be sure to delete all temporary font data files in /ttfontdata/
// to force mPDF to regenerate cached font files.
if (!defined('_OTL_OLD_SPEC_COMPAT_2'))
	define("_OTL_OLD_SPEC_COMPAT_2", true);

// Define the value used in the "head" table of a created TTF file
// 0x74727565 "true" for Mac
// 0x00010000 for Windows
// Either seems to work for a font embedded in a PDF file
// when read by Adobe Reader on a Windows PC(!)
if (!defined('_TTF_MAC_HEADER'))
	define("_TTF_MAC_HEADER", false);

// Recalculate correct metadata/profiles when making subset fonts (not SIP/SMP)
// e.g. xMin, xMax, maxNContours
if (!defined('_RECALC_PROFILE'))
	define("_RECALC_PROFILE", false);

// TrueType Font Glyph operators
define("GF_WORDS", (1 << 0));
define("GF_SCALE", (1 << 3));
define("GF_MORE", (1 << 5));
define("GF_XYSCALE", (1 << 6));
define("GF_TWOBYTWO", (1 << 7));

// mPDF 5.7.1
if (!function_exists('unicode_hex')) {

	function unicode_hex($unicode_dec)
	{
		return (sprintf("%05s", strtoupper(dechex($unicode_dec))));
	}

}

class TTFontFile
{

	var $GPOSFeatures; // mPDF 5.7.1
	var $GPOSLookups; // mPDF 5.7.1
	var $GPOSScriptLang; // mPDF 5.7.1
	var $MarkAttachmentType; // mPDF 5.7.1
	var $MarkGlyphSets; // mPDF 7.5.1
	var $GlyphClassMarks; // mPDF 5.7.1
	var $GlyphClassLigatures; // mPDF 5.7.1
	var $GlyphClassBases; // mPDF 5.7.1
	var $GlyphClassComponents; // mPDF 5.7.1
	var $GSUBScriptLang; // mPDF 5.7.1
	var $rtlPUAstr; // mPDF 5.7.1
	//var $rtlPUAarr;	// mPDF 5.7.1
	var $fontkey; // mPDF 5.7.1
	var $useOTL; // mPDF 5.7.1	var $panose;
	var $maxUni;
	var $sFamilyClass;
	var $sFamilySubClass;
	var $sipset;
	var $smpset;
	var $_pos;
	var $numTables;
	var $searchRange;
	var $entrySelector;
	var $rangeShift;
	var $tables;
	var $otables;
	var $filename;
	var $fh;
	var $glyphPos;
	var $charToGlyph;
	var $ascent;
	var $descent;
	var $lineGap; // mPDF 6
	var $hheaascent;
	var $hheadescent;
	var $hhealineGap; // mPDF 6
	var $advanceWidthMax; // mPDF 6
	var $typoAscender; // mPDF 6
	var $typoDescender; // mPDF 6
	var $typoLineGap; // mPDF 6
	var $usWinAscent; // mPDF 6
	var $usWinDescent; // mPDF 6
	var $strikeoutSize;
	var $strikeoutPosition;
	var $name;
	var $familyName;
	var $styleName;
	var $fullName;
	var $uniqueFontID;
	var $unitsPerEm;
	var $bbox;
	var $capHeight;
	var $xHeight; // mPDF 6
	var $stemV;
	var $italicAngle;
	var $flags;
	var $underlinePosition;
	var $underlineThickness;
	var $charWidths;
	var $defaultWidth;
	var $maxStrLenRead;
	var $numTTCFonts;
	var $TTCFonts;
	var $maxUniChar;
	var $kerninfo;
	var $haskernGPOS;
	var $hassmallcapsGSUB;

	public function __construct()
	{
		$this->maxStrLenRead = 200000; // Maximum size of glyf table to read in as string (otherwise reads each glyph from file)
	}

	function getMetrics($file, $fontkey, $TTCfontID = 0, $debug = false, $BMPonly = false, $useOTL = 0)
	{ // mPDF 5.7.1
		$this->useOTL = $useOTL; // mPDF 5.7.1
		$this->fontkey = $fontkey; // mPDF 5.7.1
		$this->filename = $file;
		$this->fh = fopen($file, 'rb');

		if (!$this->fh) {
			throw new MpdfException('Can\'t open file ' . $file);
		}

		$this->_pos = 0;
		$this->charWidths = '';
		$this->glyphPos = array();
		$this->charToGlyph = array();
		$this->tables = array();
		$this->otables = array();
		$this->kerninfo = array();
		$this->haskernGPOS = array();
		$this->hassmallcapsGSUB = array();
		$this->ascent = 0;
		$this->descent = 0;
		$this->lineGap = 0; // mPDF 6
		$this->hheaascent = 0; // mPDF 6
		$this->hheadescent = 0; // mPDF 6
		$this->hhealineGap = 0; // mPDF 6
		$this->xHeight = 0; // mPDF 6
		$this->capHeight = 0; // mPDF 6
		$this->panose = array();
		$this->sFamilyClass = 0;
		$this->sFamilySubClass = 0;
		$this->typoAscender = 0; // mPDF 6
		$this->typoDescender = 0; // mPDF 6
		$this->typoLineGap = 0; // mPDF 6
		$this->usWinAscent = 0; // mPDF 6
		$this->usWinDescent = 0; // mPDF 6
		$this->advanceWidthMax = 0; // mPDF 6
		$this->strikeoutSize = 0;
		$this->strikeoutPosition = 0;
		$this->numTTCFonts = 0;
		$this->TTCFonts = array();
		$this->version = $version = $this->read_ulong();
		$this->panose = array();

		if ($version == 0x4F54544F) {
			throw new MpdfException("Postscript outlines are not supported");
		}

		if ($version == 0x74746366 && !$TTCfontID) {
			throw new MpdfException("ERROR - You must define the TTCfontID for a TrueType Collection in config_fonts.php (" . $file . ")");
		}

		if (!in_array($version, array(0x00010000, 0x74727565)) && !$TTCfontID) {
			throw new MpdfException("Not a TrueType font: version=" . $version);
		}

		if ($TTCfontID > 0) {
			$this->version = $version = $this->read_ulong(); // TTC Header version now
			if (!in_array($version, array(0x00010000, 0x00020000))) {
				throw new MpdfException("ERROR - Error parsing TrueType Collection: version=" . $version . " - " . $file);
			}
			$this->numTTCFonts = $this->read_ulong();
			for ($i = 1; $i <= $this->numTTCFonts; $i++) {
				$this->TTCFonts[$i]['offset'] = $this->read_ulong();
			}
			$this->seek($this->TTCFonts[$TTCfontID]['offset']);
			$this->version = $version = $this->read_ulong(); // TTFont version again now
		}

		$this->readTableDirectory($debug);
		$this->extractInfo($debug, $BMPonly, $useOTL);
		fclose($this->fh);
	}

	function readTableDirectory($debug = false)
	{
		$this->numTables = $this->read_ushort();
		$this->searchRange = $this->read_ushort();
		$this->entrySelector = $this->read_ushort();
		$this->rangeShift = $this->read_ushort();
		$this->tables = array();
		for ($i = 0; $i < $this->numTables; $i++) {
			$record = array();
			$record['tag'] = $this->read_tag();
			$record['checksum'] = array($this->read_ushort(), $this->read_ushort());
			$record['offset'] = $this->read_ulong();
			$record['length'] = $this->read_ulong();
			$this->tables[$record['tag']] = $record;
		}
		if ($debug)
			$this->checksumTables();
	}

	function checksumTables()
	{
		// Check the checksums for all tables
		foreach ($this->tables AS $t) {
			if ($t['length'] > 0 && $t['length'] < $this->maxStrLenRead) { // 1.02
				$table = $this->get_chunk($t['offset'], $t['length']);
				$checksum = $this->calcChecksum($table);
				if ($t['tag'] == 'head') {
					$up = unpack('n*', substr($table, 8, 4));
					$adjustment[0] = $up[1];
					$adjustment[1] = $up[2];
					$checksum = $this->sub32($checksum, $adjustment);
				}
				$xchecksum = $t['checksum'];
				if ($xchecksum != $checksum) {
					throw new MpdfException(sprintf('TTF file "%s": invalid checksum %s table: %s (expected %s)', $this->filename, dechex($checksum[0]) . dechex($checksum[1]), $t['tag'], dechex($xchecksum[0]) . dechex($xchecksum[1])));
				}
			}
		}
	}

	function sub32($x, $y)
	{
		$xlo = $x[1];
		$xhi = $x[0];
		$ylo = $y[1];
		$yhi = $y[0];
		if ($ylo > $xlo) {
			$xlo += 1 << 16;
			$yhi += 1;
		}
		$reslo = $xlo - $ylo;
		if ($yhi > $xhi) {
			$xhi += 1 << 16;
		}
		$reshi = $xhi - $yhi;
		$reshi = $reshi & 0xFFFF;
		return array($reshi, $reslo);
	}

	function calcChecksum($data)
	{
		if (strlen($data) % 4) {
			$data .= str_repeat("\0", (4 - (strlen($data) % 4)));
		}
		$len = strlen($data);
		$hi = 0x0000;
		$lo = 0x0000;
		for ($i = 0; $i < $len; $i+=4) {
			$hi += (ord($data[$i]) << 8) + ord($data[$i + 1]);
			$lo += (ord($data[$i + 2]) << 8) + ord($data[$i + 3]);
			$hi += ($lo >> 16) & 0xFFFF;
			$lo = $lo & 0xFFFF;
		}
		$hi = $hi & 0xFFFF; // mPDF 5.7.1
		return array($hi, $lo);
	}

	function get_table_pos($tag)
	{
		if (!isset($this->tables[$tag])) {
			return array(0, 0);
		}
		$offset = $this->tables[$tag]['offset'];
		$length = $this->tables[$tag]['length'];
		return array($offset, $length);
	}

	function seek($pos)
	{
		$this->_pos = $pos;
		fseek($this->fh, $this->_pos);
	}

	function skip($delta)
	{
		$this->_pos = $this->_pos + $delta;
		fseek($this->fh, $delta, SEEK_CUR);
	}

	function seek_table($tag, $offset_in_table = 0)
	{
		$tpos = $this->get_table_pos($tag);
		$this->_pos = $tpos[0] + $offset_in_table;
		fseek($this->fh, $this->_pos);
		return $this->_pos;
	}

	function read_tag()
	{
		$this->_pos += 4;
		return fread($this->fh, 4);
	}

	function read_short()
	{
		$this->_pos += 2;
		$s = fread($this->fh, 2);
		$a = (ord($s[0]) << 8) + ord($s[1]);
		if ($a & (1 << 15)) {
			$a = ($a - (1 << 16));
		}
		return $a;
	}

	function unpack_short($s)
	{
		$a = (ord($s[0]) << 8) + ord($s[1]);
		if ($a & (1 << 15)) {
			$a = ($a - (1 << 16));
		}
		return $a;
	}

	function read_ushort()
	{
		$this->_pos += 2;
		$s = fread($this->fh, 2);
		return (ord($s[0]) << 8) + ord($s[1]);
	}

	function read_ulong()
	{
		$this->_pos += 4;
		$s = fread($this->fh, 4);
		// if large uInt32 as an integer, PHP converts it to -ve
		return (ord($s[0]) * 16777216) + (ord($s[1]) << 16) + (ord($s[2]) << 8) + ord($s[3]); // 	16777216  = 1<<24
	}

	function get_ushort($pos)
	{
		fseek($this->fh, $pos);
		$s = fread($this->fh, 2);
		return (ord($s[0]) << 8) + ord($s[1]);
	}

	function get_ulong($pos)
	{
		fseek($this->fh, $pos);
		$s = fread($this->fh, 4);
		// iF large uInt32 as an integer, PHP converts it to -ve
		return (ord($s[0]) * 16777216) + (ord($s[1]) << 16) + (ord($s[2]) << 8) + ord($s[3]); // 	16777216  = 1<<24
	}

	function pack_short($val)
	{
		if ($val < 0) {
			$val = abs($val);
			$val = ~$val;
			$val += 1;
		}
		return pack("n", $val);
	}

	function splice($stream, $offset, $value)
	{
		return substr($stream, 0, $offset) . $value . substr($stream, $offset + strlen($value));
	}

	function _set_ushort($stream, $offset, $value)
	{
		$up = pack("n", $value);
		return $this->splice($stream, $offset, $up);
	}

	function _set_short($stream, $offset, $val)
	{
		if ($val < 0) {
			$val = abs($val);
			$val = ~$val;
			$val += 1;
		}
		$up = pack("n", $val);
		return $this->splice($stream, $offset, $up);
	}

	function get_chunk($pos, $length)
	{
		fseek($this->fh, $pos);
		if ($length < 1) {
			return '';
		}
		return (fread($this->fh, $length));
	}

	function get_table($tag)
	{
		list($pos, $length) = $this->get_table_pos($tag);
		if ($length == 0) {
			return '';
		}
		fseek($this->fh, $pos);
		return (fread($this->fh, $length));
	}

	function add($tag, $data)
	{
		if ($tag == 'head') {
			$data = $this->splice($data, 8, "\0\0\0\0");
		}
		$this->otables[$tag] = $data;
	}

	/////////////////////////////////////////////////////////////////////////////////////////
	function getCTG($file, $TTCfontID = 0, $debug = false, $useOTL = false)
	{ // mPDF 5.7.1
		// Only called if font is not to be used as embedded subset i.e. NOT called for SIP/SMP fonts
		$this->useOTL = $useOTL; // mPDF 5.7.1
		$this->filename = $file;
		$this->fh = fopen($file, 'rb');

		if (!$this->fh) {
			throw new MpdfException('Can\'t open file ' . $file);
		}

		$this->_pos = 0;
		$this->charWidths = '';
		$this->glyphPos = array();
		$this->charToGlyph = array();
		$this->tables = array();
		$this->numTTCFonts = 0;
		$this->TTCFonts = array();
		$this->skip(4);
		if ($TTCfontID > 0) {
			$this->version = $version = $this->read_ulong(); // TTC Header version now
			if (!in_array($version, array(0x00010000, 0x00020000))) {
				throw new MpdfException("ERROR - Error parsing TrueType Collection: version=" . $version . " - " . $file);
			}
			$this->numTTCFonts = $this->read_ulong();
			for ($i = 1; $i <= $this->numTTCFonts; $i++) {
				$this->TTCFonts[$i]['offset'] = $this->read_ulong();
			}
			$this->seek($this->TTCFonts[$TTCfontID]['offset']);
			$this->version = $version = $this->read_ulong(); // TTFont version again now
		}
		$this->readTableDirectory($debug);


		// cmap - Character to glyph index mapping table
		$cmap_offset = $this->seek_table("cmap");
		$this->skip(2);
		$cmapTableCount = $this->read_ushort();
		$unicode_cmap_offset = 0;
		for ($i = 0; $i < $cmapTableCount; $i++) {
			$platformID = $this->read_ushort();
			$encodingID = $this->read_ushort();
			$offset = $this->read_ulong();
			$save_pos = $this->_pos;
			if ($platformID == 3 && $encodingID == 1) { // Microsoft, Unicode
				$format = $this->get_ushort($cmap_offset + $offset);
				if ($format == 4) {
					$unicode_cmap_offset = $cmap_offset + $offset;
					break;
				}
			} else if ($platformID == 0) { // Unicode -- assume all encodings are compatible
				$format = $this->get_ushort($cmap_offset + $offset);
				if ($format == 4) {
					$unicode_cmap_offset = $cmap_offset + $offset;
					break;
				}
			}
			$this->seek($save_pos);
		}

		$glyphToChar = array();
		$charToGlyph = array();
		$this->getCMAP4($unicode_cmap_offset, $glyphToChar, $charToGlyph);

		///////////////////////////////////
		// mPDF 5.7.1
		// Map Unmapped glyphs - from $numGlyphs
		if ($useOTL) {
			$this->seek_table("maxp");
			$this->skip(4);
			$numGlyphs = $this->read_ushort();
			$bctr = 0xE000;
			for ($gid = 1; $gid < $numGlyphs; $gid++) {
				if (!isset($glyphToChar[$gid])) {
					while (isset($charToGlyph[$bctr])) {
						$bctr++;
					} // Avoid overwriting a glyph already mapped in PUA
					if ($bctr > 0xF8FF) {
						throw new MpdfException($file . " : WARNING - Font cannot map all included glyphs into Private Use Area U+E000 - U+F8FF; cannot use useOTL on this font");
					}
					$glyphToChar[$gid][] = $bctr;
					$charToGlyph[$bctr] = $gid;
					$bctr++;
				}
			}
		}
		///////////////////////////////////

		fclose($this->fh);
		return ($charToGlyph);
	}

	/////////////////////////////////////////////////////////////////////////////////////////
	function getTTCFonts($file)
	{
		$this->filename = $file;
		$this->fh = fopen($file, 'rb');
		if (!$this->fh) {
			return ('ERROR - Can\'t open file ' . $file);
		}
		$this->numTTCFonts = 0;
		$this->TTCFonts = array();
		$this->version = $version = $this->read_ulong();
		if ($version == 0x74746366) {
			$this->version = $version = $this->read_ulong(); // TTC Header version now
			if (!in_array($version, array(0x00010000, 0x00020000)))
				return("ERROR - Error parsing TrueType Collection: version=" . $version . " - " . $file);
		}
		else {
			return("ERROR - Not a TrueType Collection: version=" . $version . " - " . $file);
		}
		$this->numTTCFonts = $this->read_ulong();
		for ($i = 1; $i <= $this->numTTCFonts; $i++) {
			$this->TTCFonts[$i]['offset'] = $this->read_ulong();
		}
	}

	/////////////////////////////////////////////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////////////////////////////

	function extractInfo($debug = false, $BMPonly = false, $useOTL = 0)
	{
		// Values are all set to 0 or blank at start of getMetrics
		///////////////////////////////////
		// name - Naming table
		///////////////////////////////////
		$name_offset = $this->seek_table("name");
		$format = $this->read_ushort();
		if ($format != 0 && $format != 1)
			throw new MpdfException("Unknown name table format " . $format);
		$numRecords = $this->read_ushort();
		$string_data_offset = $name_offset + $this->read_ushort();
		$names = array(1 => '', 2 => '', 3 => '', 4 => '', 6 => '');
		$K = array_keys($names);
		$nameCount = count($names);
		for ($i = 0; $i < $numRecords; $i++) {
			$platformId = $this->read_ushort();
			$encodingId = $this->read_ushort();
			$languageId = $this->read_ushort();
			$nameId = $this->read_ushort();
			$length = $this->read_ushort();
			$offset = $this->read_ushort();
			if (!in_array($nameId, $K))
				continue;
			$N = '';
			if ($platformId == 3 && $encodingId == 1 && $languageId == 0x409) { // Microsoft, Unicode, US English, PS Name
				$opos = $this->_pos;
				$this->seek($string_data_offset + $offset);
				if ($length % 2 != 0)
					throw new MpdfException("PostScript name is UTF-16BE string of odd length");
				$length /= 2;
				$N = '';
				while ($length > 0) {
					$char = $this->read_ushort();
					$N .= (chr($char));
					$length -= 1;
				}
				$this->_pos = $opos;
				$this->seek($opos);
			} else if ($platformId == 1 && $encodingId == 0 && $languageId == 0) { // Macintosh, Roman, English, PS Name
				$opos = $this->_pos;
				$N = $this->get_chunk($string_data_offset + $offset, $length);
				$this->_pos = $opos;
				$this->seek($opos);
			}
			if ($N && $names[$nameId] == '') {
				$names[$nameId] = $N;
				$nameCount -= 1;
				if ($nameCount == 0)
					break;
			}
		}
		if ($names[6])
			$psName = $names[6];
		else if ($names[4])
			$psName = preg_replace('/ /', '-', $names[4]);
		else if ($names[1])
			$psName = preg_replace('/ /', '-', $names[1]);
		else
			$psName = '';
		if (!$psName)
			throw new MpdfException("Could not find PostScript font name: " . $this->filename);
		// CHECK IF psName valid (PadaukBook contains illegal characters in Name ID 6 i.e. Postscript Name)
		$psNameInvalid = false;
		for ($i = 0; $i < count($psName); $i++) {
			$c = $psName[$i];
			$oc = ord($c);
			if ($oc > 126 || strpos(' [](){}<>/%', $c) !== false) {
				//throw new MpdfException("psName=".$psName." contains invalid character ".$c." ie U+".ord(c));
				$psNameInvalid = true;
				break;
			}
		}

		if ($psNameInvalid && $names[4])
			$psName = preg_replace('/ /', '-', $names[4]);


		$this->name = $psName;
		if ($names[1]) {
			$this->familyName = $names[1];
		} else {
			$this->familyName = $psName;
		}
		if ($names[2]) {
			$this->styleName = $names[2];
		} else {
			$this->styleName = 'Regular';
		}
		if ($names[4]) {
			$this->fullName = $names[4];
		} else {
			$this->fullName = $psName;
		}
		if ($names[3]) {
			$this->uniqueFontID = $names[3];
		} else {
			$this->uniqueFontID = $psName;
		}

		if (!$psNameInvalid && $names[6]) {
			$this->fullName = $names[6];
		}

		///////////////////////////////////
		// head - Font header table
		///////////////////////////////////
		$this->seek_table("head");
		if ($debug) {
			$ver_maj = $this->read_ushort();
			$ver_min = $this->read_ushort();
			if ($ver_maj != 1)
				throw new MpdfException('Unknown head table version ' . $ver_maj . '.' . $ver_min);
			$this->fontRevision = $this->read_ushort() . $this->read_ushort();

			$this->skip(4);
			$magic = $this->read_ulong();
			if ($magic != 0x5F0F3CF5)
				throw new MpdfException('Invalid head table magic ' . $magic);
			$this->skip(2);
		}
		else {
			$this->skip(18);
		}
		$this->unitsPerEm = $unitsPerEm = $this->read_ushort();
		$scale = 1000 / $unitsPerEm;
		$this->skip(16);
		$xMin = $this->read_short();
		$yMin = $this->read_short();
		$xMax = $this->read_short();
		$yMax = $this->read_short();
		$this->bbox = array(($xMin * $scale), ($yMin * $scale), ($xMax * $scale), ($yMax * $scale));

		$this->skip(3 * 2);
		$indexToLocFormat = $this->read_ushort();
		$glyphDataFormat = $this->read_ushort();
		if ($glyphDataFormat != 0) {
			throw new MpdfException('Unknown glyph data format ' . $glyphDataFormat);
		}

		///////////////////////////////////
		// hhea metrics table
		///////////////////////////////////
		if (isset($this->tables["hhea"])) {
			$this->seek_table("hhea");
			$this->skip(4);
			$hheaAscender = $this->read_short();
			$hheaDescender = $this->read_short();
			$hheaLineGap = $this->read_short(); // mPDF 6
			$hheaAdvanceWidthMax = $this->read_ushort(); // mPDF 6
			$this->hheaascent = ($hheaAscender * $scale);
			$this->hheadescent = ($hheaDescender * $scale);
			$this->hhealineGap = ($hheaLineGap * $scale); // mPDF 6
			$this->advanceWidthMax = ($hheaAdvanceWidthMax * $scale); // mPDF 6
		}

		///////////////////////////////////
		// OS/2 - OS/2 and Windows metrics table
		///////////////////////////////////
		$use_typo_metrics = false;
		if (isset($this->tables["OS/2"])) {
			$this->seek_table("OS/2");
			$version = $this->read_ushort();
			$this->skip(2);
			$usWeightClass = $this->read_ushort();
			$this->skip(2);
			$fsType = $this->read_ushort();
			if ($fsType == 0x0002 || ($fsType & 0x0300) != 0) {
				$this->restrictedUse = true;
			}

			// mPDF 6
			$this->skip(16);
			$yStrikeoutSize = $this->read_short();
			$yStrikeoutPosition = $this->read_short();
			$this->strikeoutSize = ($yStrikeoutSize * $scale);
			$this->strikeoutPosition = ($yStrikeoutPosition * $scale);

			$sF = $this->read_short();
			$this->sFamilyClass = ($sF >> 8);
			$this->sFamilySubClass = ($sF & 0xFF);
			$this->_pos += 10; //PANOSE = 10 byte length
			$panose = fread($this->fh, 10);
			$this->panose = array();
			for ($p = 0; $p < strlen($panose); $p++) {
				$this->panose[] = ord($panose[$p]);
			}
			//$this->skip(26);
			// mPDF 6
			$this->skip(20);
			$fsSelection = $this->read_ushort();
			$use_typo_metrics = (($fsSelection & 0x80) == 0x80); // bit#7 = USE_TYPO_METRICS
			$this->skip(4);

			$sTypoAscender = $this->read_short();
			$sTypoDescender = $this->read_short();
			$sTypoLineGap = $this->read_short(); // mPDF 6
			if ($sTypoAscender)
				$this->typoAscender = ($sTypoAscender * $scale); // mPDF 6
			if ($sTypoDescender)
				$this->typoDescender = ($sTypoDescender * $scale); // mPDF 6
			if ($sTypoLineGap)
				$this->typoLineGap = ($sTypoLineGap * $scale); // mPDF 6

			$usWinAscent = $this->read_ushort(); // mPDF 6
			$usWinDescent = $this->read_ushort(); // mPDF 6
			if ($usWinAscent)
				$this->usWinAscent = ($usWinAscent * $scale); // mPDF 6
			if ($usWinDescent)
				$this->usWinDescent = ($usWinDescent * $scale); // mPDF 6

			if ($version > 1) {
				$this->skip(8); // mPDF 6
				$sxHeight = $this->read_short();
				$this->xHeight = ($sxHeight * $scale);
				$sCapHeight = $this->read_short();
				$this->capHeight = ($sCapHeight * $scale);
			}
		} else {
			$usWeightClass = 400;
		}
		$this->stemV = 50 + intval(pow(($usWeightClass / 65.0), 2));


		// FONT DESCRIPTOR METRICS
		if (_FONT_DESCRIPTOR == 'winTypo') {
			$this->ascent = $this->typoAscender;
			$this->descent = $this->typoDescender;
			$this->lineGap = $this->typoLineGap;
		} else if (_FONT_DESCRIPTOR == 'mac') {
			$this->ascent = $this->hheaascent;
			$this->descent = $this->hheadescent;
			$this->lineGap = $this->hhealineGap;
		} else { // if (_FONT_DESCRIPTOR == 'win') {	// default
			$this->ascent = $this->usWinAscent;
			$this->descent = -$this->usWinDescent;
			$this->lineGap = 0;

			/* Special case - if either the winAscent or winDescent are greater than the
			  font bounding box yMin yMax, then reduce them accordingly.
			  This works with Myanmar Text (Windows 8 version) to give a
			  line-height normal that is equivalent to that produced in browsers.
			  Also Khmer OS = compatible with MSWord, Wordpad and browser. */
			if ($this->ascent > $this->bbox[3]) {
				$this->ascent = $this->bbox[3];
			}
			if ($this->descent < $this->bbox[1]) {
				$this->descent = $this->bbox[1];
			}


			/* Override case - if the USE_TYPO_METRICS bit is set on OS/2 fsSelection
			  this is telling the font to use the sTypo values and not the usWinAscent values.
			  This works as a fix with Cambria Math to give a normal line-height;
			  at present, this is the only font I have found with this bit set;
			  although note that MS WordPad and windows FF browser uses the big line-height from winAscent
			  but Word 2007 get it right . */
			if ($use_typo_metrics && $this->typoAscender) {
				$this->ascent = $this->typoAscender;
				$this->descent = $this->typoDescender;
				$this->lineGap = $this->typoLineGap;
			}
		}


		///////////////////////////////////
		// post - PostScript table
		///////////////////////////////////
		$this->seek_table("post");
		if ($debug) {
			$ver_maj = $this->read_ushort();
			$ver_min = $this->read_ushort();
			if ($ver_maj < 1 || $ver_maj > 4)
				throw new MpdfException('Unknown post table version ' . $ver_maj);
		}
		else {
			$this->skip(4);
		}
		$this->italicAngle = $this->read_short() + $this->read_ushort() / 65536.0;
		$this->underlinePosition = $this->read_short() * $scale;
		$this->underlineThickness = $this->read_short() * $scale;
		$isFixedPitch = $this->read_ulong();

		$this->flags = 4;

		if ($this->italicAngle != 0)
			$this->flags = $this->flags | 64;
		if ($usWeightClass >= 600)
			$this->flags = $this->flags | 262144;
		if ($isFixedPitch)
			$this->flags = $this->flags | 1;

		///////////////////////////////////
		// hhea - Horizontal header table
		///////////////////////////////////
		$this->seek_table("hhea");
		if ($debug) {
			$ver_maj = $this->read_ushort();
			$ver_min = $this->read_ushort();
			if ($ver_maj != 1)
				throw new MpdfException('Unknown hhea table version ' . $ver_maj);
			$this->skip(28);
		}
		else {
			$this->skip(32);
		}

		$metricDataFormat = $this->read_ushort();

		if ($metricDataFormat != 0) {
			throw new MpdfException('Unknown horizontal metric data format ' . $metricDataFormat);
		}

		$numberOfHMetrics = $this->read_ushort();

		if ($numberOfHMetrics == 0) {
			throw new MpdfException('Number of horizontal metrics is 0');
		}

		///////////////////////////////////
		// maxp - Maximum profile table
		///////////////////////////////////
		$this->seek_table("maxp");
		if ($debug) {
			$ver_maj = $this->read_ushort();
			$ver_min = $this->read_ushort();
			if ($ver_maj != 1) {
				throw new MpdfException('Unknown maxp table version ' . $ver_maj);
			}
		}
		else {
			$this->skip(4);
		}
		$numGlyphs = $this->read_ushort();


		///////////////////////////////////
		// cmap - Character to glyph index mapping table
		///////////////////////////////////
		$cmap_offset = $this->seek_table("cmap");
		$this->skip(2);
		$cmapTableCount = $this->read_ushort();
		$unicode_cmap_offset = 0;
		for ($i = 0; $i < $cmapTableCount; $i++) {
			$platformID = $this->read_ushort();
			$encodingID = $this->read_ushort();
			$offset = $this->read_ulong();
			$save_pos = $this->_pos;
			if (($platformID == 3 && $encodingID == 1) || $platformID == 0) { // Microsoft, Unicode
				$format = $this->get_ushort($cmap_offset + $offset);
				if ($format == 4) {
					if (!$unicode_cmap_offset)
						$unicode_cmap_offset = $cmap_offset + $offset;
					if ($BMPonly)
						break;
				}
			}
			// Microsoft, Unicode Format 12 table HKCS
			else if ((($platformID == 3 && $encodingID == 10) || $platformID == 0) && !$BMPonly) {
				$format = $this->get_ushort($cmap_offset + $offset);
				if ($format == 12) {
					$unicode_cmap_offset = $cmap_offset + $offset;
					break;
				}
			}
			$this->seek($save_pos);
		}

		if (!$unicode_cmap_offset) {
			throw new MpdfException('Font (' . $this->filename . ') does not have cmap for Unicode (platform 3, encoding 1, format 4, or platform 0, any encoding, format 4)');
		}

		$sipset = false;
		$smpset = false;

		// mPDF 5.7.1
		$this->rtlPUAstr = '';
		//$this->rtlPUAarr = array();
		$this->GSUBScriptLang = array();
		$this->GSUBFeatures = array();
		$this->GSUBLookups = array();
		$this->GPOSScriptLang = array();
		$this->GPOSFeatures = array();
		$this->GPOSLookups = array();
		$this->glyphIDtoUni = '';

		// Format 12 CMAP does characters above Unicode BMP i.e. some HKCS characters U+20000 and above
		if ($format == 12 && !$BMPonly) {
			$this->maxUniChar = 0;
			$this->seek($unicode_cmap_offset + 4);
			$length = $this->read_ulong();
			$limit = $unicode_cmap_offset + $length;
			$this->skip(4);

			$nGroups = $this->read_ulong();

			$glyphToChar = array();
			$charToGlyph = array();
			for ($i = 0; $i < $nGroups; $i++) {
				$startCharCode = $this->read_ulong();
				$endCharCode = $this->read_ulong();
				$startGlyphCode = $this->read_ulong();
				// ZZZ98
				if ($endCharCode > 0x20000 && $endCharCode < 0x2FFFF) {
					$sipset = true;
				} else if ($endCharCode > 0x10000 && $endCharCode < 0x1FFFF) {
					$smpset = true;
				}
				$offset = 0;
				for ($unichar = $startCharCode; $unichar <= $endCharCode; $unichar++) {
					$glyph = $startGlyphCode + $offset;
					$offset++;
					// ZZZ98
					if ($unichar < 0x30000) {
						$charToGlyph[$unichar] = $glyph;
						$this->maxUniChar = max($unichar, $this->maxUniChar);
						$glyphToChar[$glyph][] = $unichar;
					}
				}
			}
		} else {

			$glyphToChar = array();
			$charToGlyph = array();
			$this->getCMAP4($unicode_cmap_offset, $glyphToChar, $charToGlyph);
		}
		$this->sipset = $sipset;
		$this->smpset = $smpset;

		///////////////////////////////////
		// mPDF 5.7.1
		// Map Unmapped glyphs (or glyphs mapped to upper PUA U+F00000 onwards i.e. > U+2FFFF) - from $numGlyphs
		if ($this->useOTL) {
			$bctr = 0xE000;
			for ($gid = 1; $gid < $numGlyphs; $gid++) {
				if (!isset($glyphToChar[$gid])) {
					while (isset($charToGlyph[$bctr])) {
						$bctr++;
					} // Avoid overwriting a glyph already mapped in PUA
					// ZZZ98
					if (($bctr > 0xF8FF) && ($bctr < 0x2CEB0)) {
						if (!$BMPonly) {
							$bctr = 0x2CEB0; // Use unassigned area 0x2CEB0 to 0x2F7FF (space for 10,000 characters)
							$this->sipset = $sipset = true; // forces subsetting; also ensure charwidths are saved
							while (isset($charToGlyph[$bctr])) {
								$bctr++;
							}
						} else {
							throw new MpdfException($names[1] . " : WARNING - The font does not have enough space to map all (unmapped) included glyphs into Private Use Area U+E000 - U+F8FF");
						}
					}
					$glyphToChar[$gid][] = $bctr;
					$charToGlyph[$bctr] = $gid;
					$this->maxUniChar = max($bctr, $this->maxUniChar);
					$bctr++;
				}
			}
		}

		$this->glyphToChar = $glyphToChar;
		///////////////////////////////////
		// mPDF 5.7.1	OpenType Layout tables
		$this->GSUBScriptLang = array();
		$this->rtlPUAstr = '';
		//$this->rtlPUAarr = array();
		if ($useOTL) {
			$this->_getGDEFtables();
			list($this->GSUBScriptLang, $this->GSUBFeatures, $this->GSUBLookups, $this->rtlPUAstr) = $this->_getGSUBtables();
			// , $this->rtlPUAarr not needed
			list($this->GPOSScriptLang, $this->GPOSFeatures, $this->GPOSLookups) = $this->_getGPOStables();
			$this->glyphIDtoUni = str_pad('', 256 * 256 * 3, "\x00");
			foreach ($glyphToChar AS $gid => $arr) {
				if (isset($glyphToChar[$gid][0])) {
					$char = $glyphToChar[$gid][0];

					if ($char != 0 && $char != 65535) {
						$this->glyphIDtoUni[$gid * 3] = chr($char >> 16);
						$this->glyphIDtoUni[$gid * 3 + 1] = chr(($char >> 8) & 0xFF);
						$this->glyphIDtoUni[$gid * 3 + 2] = chr($char & 0xFF);
					}
				}
			}
		}
		///////////////////////////////////
		// if xHeight and/or CapHeight are not available from OS/2 (e.g. eraly versions)
		// Calculate from yMax of 'x' or 'H' Glyphs...
		if ($this->xHeight == 0) {
			if (isset($charToGlyph[0x78])) {
				$gidx = $charToGlyph[0x78]; // U+0078 (LATIN SMALL LETTER X)
				$start = $this->seek_table('loca');
				if ($indexToLocFormat == 0) {
					$this->skip($gidx * 2);
					$locax = $this->read_ushort() * 2;
				} else if ($indexToLocFormat == 1) {
					$this->skip($gidx * 4);
					$locax = $this->read_ulong();
				}
				$start = $this->seek_table('glyf');
				$this->skip($locax);
				$this->skip(8);
				$yMaxx = $this->read_short();
				$this->xHeight = $yMaxx * $scale;
			}
		}
		if ($this->capHeight == 0) {
			if (isset($charToGlyph[0x48])) {
				$gidH = $charToGlyph[0x48]; // U+0048 (LATIN CAPITAL LETTER H)
				$start = $this->seek_table('loca');
				if ($indexToLocFormat == 0) {
					$this->skip($gidH * 2);
					$locaH = $this->read_ushort() * 2;
				} else if ($indexToLocFormat == 1) {
					$this->skip($gidH * 4);
					$locaH = $this->read_ulong();
				}
				$start = $this->seek_table('glyf');
				$this->skip($locaH);
				$this->skip(8);
				$yMaxH = $this->read_short();
				$this->capHeight = $yMaxH * $scale;
			} else {
				$this->capHeight = $this->ascent;
			} // final default is to set it = to Ascent
		}




		///////////////////////////////////
		// hmtx - Horizontal metrics table
		///////////////////////////////////
		$this->getHMTX($numberOfHMetrics, $numGlyphs, $glyphToChar, $scale);

		///////////////////////////////////
		// kern - Kerning pair table
		///////////////////////////////////
		// Recognises old form of Kerning table - as required by Windows - Format 0 only
		$kern_offset = $this->seek_table("kern");
		$version = $this->read_ushort();
		$nTables = $this->read_ushort();
		// subtable header
		$sversion = $this->read_ushort();
		$slength = $this->read_ushort();
		$scoverage = $this->read_ushort();
		$format = $scoverage >> 8;
		if ($kern_offset && $version == 0 && $format == 0) {
			// Format 0
			$nPairs = $this->read_ushort();
			$this->skip(6);
			for ($i = 0; $i < $nPairs; $i++) {
				$left = $this->read_ushort();
				$right = $this->read_ushort();
				$val = $this->read_short();
				if (count($glyphToChar[$left]) == 1 && count($glyphToChar[$right]) == 1) {
					if ($left != 32 && $right != 32) {
						$this->kerninfo[$glyphToChar[$left][0]][$glyphToChar[$right][0]] = intval($val * $scale);
					}
				}
			}
		}
	}

	/////////////////////////////////////////////////////////////////////////////////////////
	function _getGDEFtables()
	{
		///////////////////////////////////
		// GDEF - Glyph Definition
		///////////////////////////////////
		// http://www.microsoft.com/typography/otspec/gdef.htm
		if (isset($this->tables["GDEF"])) {
			$gdef_offset = $this->seek_table("GDEF");
			// ULONG Version of the GDEF table-currently 0x00010000
			$ver_maj = $this->read_ushort();
			$ver_min = $this->read_ushort();
			$GlyphClassDef_offset = $this->read_ushort();
			$AttachList_offset = $this->read_ushort();
			$LigCaretList_offset = $this->read_ushort();
			$MarkAttachClassDef_offset = $this->read_ushort();
			// Version 0x00010002 of GDEF header contains additional Offset to a list defining mark glyph set definitions (MarkGlyphSetDef)
			if ($ver_min == 2) {
				$MarkGlyphSetsDef_offset = $this->read_ushort();
			}

			// GlyphClassDef
			if ($GlyphClassDef_offset) {
				$this->seek($gdef_offset + $GlyphClassDef_offset);
				/*
				  1	Base glyph (single character, spacing glyph)
				  2	Ligature glyph (multiple character, spacing glyph)
				  3	Mark glyph (non-spacing combining glyph)
				  4	Component glyph (part of single character, spacing glyph)
				 */
				$GlyphByClass = $this->_getClassDefinitionTable();
			} else {
				$GlyphByClass = array();
			}

			if (isset($GlyphByClass[1]) && count($GlyphByClass[1]) > 0) {
				$this->GlyphClassBases = ' ' . implode('| ', $GlyphByClass[1]);
			} else {
				$this->GlyphClassBases = '';
			}
			if (isset($GlyphByClass[2]) && count($GlyphByClass[2]) > 0) {
				$this->GlyphClassLigatures = ' ' . implode('| ', $GlyphByClass[2]);
			} else {
				$this->GlyphClassLigatures = '';
			}
			if (isset($GlyphByClass[3]) && count($GlyphByClass[3]) > 0) {
				$this->GlyphClassMarks = ' ' . implode('| ', $GlyphByClass[3]);
			} else {
				$this->GlyphClassMarks = '';
			}
			if (isset($GlyphByClass[4]) && count($GlyphByClass[4]) > 0) {
				$this->GlyphClassComponents = ' ' . implode('| ', $GlyphByClass[4]);
			} else {
				$this->GlyphClassComponents = '';
			}

			if (isset($GlyphByClass[3]) && count($GlyphByClass[3]) > 0) {
				$Marks = $GlyphByClass[3];
			} // to use for MarkAttachmentType
			else {
				$Marks = array();
			}



			/* Required for GPOS
			  // Attachment List
			  if ($AttachList_offset) {
			  $this->seek($gdef_offset+$AttachList_offset );
			  }
			  The Attachment Point List table (AttachmentList) identifies all the attachment points defined in the GPOS table and their associated glyphs so a client can quickly access coordinates for each glyph's attachment points. As a result, the client can cache coordinates for attachment points along with glyph bitmaps and avoid recalculating the attachment points each time it displays a glyph. Without this table, processing speed would be slower because the client would have to decode the GPOS lookups that define attachment points and compile the points in a list.

			  The Attachment List table (AttachList) may be used to cache attachment point coordinates along with glyph bitmaps.

			  The table consists of an offset to a Coverage table (Coverage) listing all glyphs that define attachment points in the GPOS table, a count of the glyphs with attachment points (GlyphCount), and an array of offsets to AttachPoint tables (AttachPoint). The array lists the AttachPoint tables, one for each glyph in the Coverage table, in the same order as the Coverage Index.
			  AttachList table
			  Type 	Name 	Description
			  Offset 	Coverage 	Offset to Coverage table - from beginning of AttachList table
			  uint16 	GlyphCount 	Number of glyphs with attachment points
			  Offset 	AttachPoint[GlyphCount] 	Array of offsets to AttachPoint tables-from beginning of AttachList table-in Coverage Index order

			  An AttachPoint table consists of a count of the attachment points on a single glyph (PointCount) and an array of contour indices of those points (PointIndex), listed in increasing numerical order.

			  AttachPoint table
			  Type 	Name 	Description
			  uint16 	PointCount 	Number of attachment points on this glyph
			  uint16 	PointIndex[PointCount] 	Array of contour point indices -in increasing numerical order

			  See Example 3 - http://www.microsoft.com/typography/otspec/gdef.htm
			 */


			// Ligature Caret List
			// The Ligature Caret List table (LigCaretList) defines caret positions for all the ligatures in a font.
			// Not required for mDPF
			// MarkAttachmentType
			if ($MarkAttachClassDef_offset) {
				$this->seek($gdef_offset + $MarkAttachClassDef_offset);
				$MarkAttachmentTypes = $this->_getClassDefinitionTable();
				foreach ($MarkAttachmentTypes AS $class => $glyphs) {

					if (is_array($Marks) && count($Marks)) {
						$mat = array_diff($Marks, $MarkAttachmentTypes[$class]);
						sort($mat, SORT_STRING);
					} else {
						$mat = array();
					}

					$this->MarkAttachmentType[$class] = ' ' . implode('| ', $mat);
				}
			} else {
				$this->MarkAttachmentType = array();
			}


			// MarkGlyphSets only in Version 0x00010002 of GDEF
			if ($ver_min == 2 && $MarkGlyphSetsDef_offset) {
				$this->seek($gdef_offset + $MarkGlyphSetsDef_offset);
				$MarkSetTableFormat = $this->read_ushort();
				$MarkSetCount = $this->read_ushort();
				$MarkSetOffset = array();
				for ($i = 0; $i < $MarkSetCount; $i++) {
					$MarkSetOffset[] = $this->read_ulong();
				}
				for ($i = 0; $i < $MarkSetCount; $i++) {
					$this->seek($MarkSetOffset[$i]);
					$glyphs = $this->_getCoverage();
					$this->MarkGlyphSets[$i] = ' ' . implode('| ', $glyphs);
				}
			} else {
				$this->MarkGlyphSets = array();
			}
		} else {
			throw new MpdfException('Warning - You cannot set this font (' . $this->filename . ') to use OTL, as it does not include OTL tables (or at least, not a GDEF table).');
		}

		//=====================================================================================
		//=====================================================================================
		//=====================================================================================
		$GSUB_offset = 0;
		$GPOS_offset = 0;
		$GSUB_length = 0;
		$s = '';
		if (isset($this->tables["GSUB"])) {
			$GSUB_offset = $this->seek_table("GSUB");
			$GSUB_length = $this->tables["GSUB"]['length'];
			$s .= fread($this->fh, $this->tables["GSUB"]['length']);
		}
		if (isset($this->tables["GPOS"])) {
			$GPOS_offset = $this->seek_table("GPOS");
			$s .= fread($this->fh, $this->tables["GPOS"]['length']);
		}
		if ($s)
			file_put_contents(_MPDF_TTFONTDATAPATH . $this->fontkey . '.GSUBGPOStables.dat', $s);

		//=====================================================================================
		//=====================================================================================

		$s = '<?php
$GSUB_offset = ' . $GSUB_offset . ';
$GPOS_offset = ' . $GPOS_offset . ';
$GSUB_length = ' . $GSUB_length . ';
$GlyphClassBases = \'' . $this->GlyphClassBases . '\';
$GlyphClassMarks = \'' . $this->GlyphClassMarks . '\';
$GlyphClassLigatures = \'' . $this->GlyphClassLigatures . '\';
$GlyphClassComponents = \'' . $this->GlyphClassComponents . '\';
$MarkGlyphSets = ' . var_export($this->MarkGlyphSets, true) . ';
$MarkAttachmentType = ' . var_export($this->MarkAttachmentType, true) . ';
?>';


		file_put_contents(_MPDF_TTFONTDATAPATH . $this->fontkey . '.GDEFdata.php', $s);

		//=====================================================================================
//echo $this->GlyphClassMarks ; exit;
//print_r($GlyphClass); exit;
//print_r($GlyphByClass); exit;
	}

	function _getClassDefinitionTable()
	{

		// NB Any glyph not included in the range of covered GlyphIDs automatically belongs to Class 0. This is not returned by this function
		$ClassFormat = $this->read_ushort();
		$GlyphByClass = array();
		if ($ClassFormat == 1) {
			$StartGlyph = $this->read_ushort();
			$GlyphCount = $this->read_ushort();
			for ($i = 0; $i < $GlyphCount; $i++) {
				$gid = $StartGlyph + $i;
				$class = $this->read_ushort();
				// Several fonts  (mainly dejavu.../Freeserif etc) have a MarkAttachClassDef Format 1, where StartGlyph is 0 and GlyphCount is 1
				// This doesn't seem to do anything useful?
				// Freeserif does not have $this->glyphToChar[0] allocated and would throw an error, so check if isset:
				if (isset($this->glyphToChar[$gid][0])) {
					$GlyphByClass[$class][] = unicode_hex($this->glyphToChar[$gid][0]);
				}
			}
		} else if ($ClassFormat == 2) {
			$tableCount = $this->read_ushort();
			for ($i = 0; $i < $tableCount; $i++) {
				$startGlyphID = $this->read_ushort();
				$endGlyphID = $this->read_ushort();
				$class = $this->read_ushort();
				for ($gid = $startGlyphID; $gid <= $endGlyphID; $gid++) {
					if (isset($this->glyphToChar[$gid][0])) {
						$GlyphByClass[$class][] = unicode_hex($this->glyphToChar[$gid][0]);
					}
				}
			}
		}
		foreach ($GlyphByClass AS $class => $glyphs) {
			sort($GlyphByClass[$class], SORT_STRING); // SORT makes it easier to read in development ? order not important ???
		}
		ksort($GlyphByClass);
		return $GlyphByClass;
	}

	function _getGSUBtables()
	{
		///////////////////////////////////
		// GSUB - Glyph Substitution
		///////////////////////////////////
		if (isset($this->tables["GSUB"])) {
			$ffeats = array();
			$gsub_offset = $this->seek_table("GSUB");
			$this->skip(4);
			$ScriptList_offset = $gsub_offset + $this->read_ushort();
			$FeatureList_offset = $gsub_offset + $this->read_ushort();
			$LookupList_offset = $gsub_offset + $this->read_ushort();

			// ScriptList
			$this->seek($ScriptList_offset);
			$ScriptCount = $this->read_ushort();
			for ($i = 0; $i < $ScriptCount; $i++) {
				$ScriptTag = $this->read_tag(); // = "beng", "deva" etc.
				$ScriptTableOffset = $this->read_ushort();
				$ffeats[$ScriptTag] = $ScriptList_offset + $ScriptTableOffset;
			}

			// Script Table
			foreach ($ffeats AS $t => $o) {
				$ls = array();
				$this->seek($o);
				$DefLangSys_offset = $this->read_ushort();
				if ($DefLangSys_offset > 0) {
					$ls['DFLT'] = $DefLangSys_offset + $o;
				}
				$LangSysCount = $this->read_ushort();
				for ($i = 0; $i < $LangSysCount; $i++) {
					$LangTag = $this->read_tag(); // =
					$LangTableOffset = $this->read_ushort();
					$ls[$LangTag] = $o + $LangTableOffset;
				}
				$ffeats[$t] = $ls;
			}
//print_r($ffeats); exit;
			// Get FeatureIndexList
			// LangSys Table - from first listed langsys
			foreach ($ffeats AS $st => $scripts) {
				foreach ($scripts AS $t => $o) {
					$FeatureIndex = array();
					$langsystable_offset = $o;
					$this->seek($langsystable_offset);
					$LookUpOrder = $this->read_ushort(); //==NULL
					$ReqFeatureIndex = $this->read_ushort();
					if ($ReqFeatureIndex != 0xFFFF) {
						$FeatureIndex[] = $ReqFeatureIndex;
					}
					$FeatureCount = $this->read_ushort();
					for ($i = 0; $i < $FeatureCount; $i++) {
						$FeatureIndex[] = $this->read_ushort(); // = index of feature
					}
					$ffeats[$st][$t] = $FeatureIndex;
				}
			}
//print_r($ffeats); exit;
			// Feauture List => LookupListIndex es
			$this->seek($FeatureList_offset);
			$FeatureCount = $this->read_ushort();
			$Feature = array();
			for ($i = 0; $i < $FeatureCount; $i++) {
				$tag = $this->read_tag();
				if ($tag == 'smcp') {
					$this->hassmallcapsGSUB = true;
				}
				$Feature[$i] = array('tag' => $tag);
				$Feature[$i]['offset'] = $FeatureList_offset + $this->read_ushort();
			}
			for ($i = 0; $i < $FeatureCount; $i++) {
				$this->seek($Feature[$i]['offset']);
				$this->read_ushort(); // null [FeatureParams]
				$Feature[$i]['LookupCount'] = $Lookupcount = $this->read_ushort();
				$Feature[$i]['LookupListIndex'] = array();
				for ($c = 0; $c < $Lookupcount; $c++) {
					$Feature[$i]['LookupListIndex'][] = $this->read_ushort();
				}
			}

//print_r($Feature); exit;

			foreach ($ffeats AS $st => $scripts) {
				foreach ($scripts AS $t => $o) {
					$FeatureIndex = $ffeats[$st][$t];
					foreach ($FeatureIndex AS $k => $fi) {
						$ffeats[$st][$t][$k] = $Feature[$fi];
					}
				}
			}
			//=====================================================================================
			$gsub = array();
			$GSUBScriptLang = array();
			foreach ($ffeats AS $st => $scripts) {
				foreach ($scripts AS $t => $langsys) {
					$lg = array();
					foreach ($langsys AS $ft) {
						$lg[$ft['LookupListIndex'][0]] = $ft;
					}
					// list of Lookups in order they need to be run i.e. order listed in Lookup table
					ksort($lg);
					foreach ($lg AS $ft) {
						$gsub[$st][$t][$ft['tag']] = $ft['LookupListIndex'];
					}
					if (!isset($GSUBScriptLang[$st])) {
						$GSUBScriptLang[$st] = '';
					}
					$GSUBScriptLang[$st] .= $t . ' ';
				}
			}

//print_r($gsub); exit;
			//=====================================================================================
			// Get metadata and offsets for whole Lookup List table
			$this->seek($LookupList_offset);
			$LookupCount = $this->read_ushort();
			$GSLookup = array();
			$Offsets = array();
			$SubtableCount = array();
			for ($i = 0; $i < $LookupCount; $i++) {
				$Offsets[$i] = $LookupList_offset + $this->read_ushort();
			}
			for ($i = 0; $i < $LookupCount; $i++) {
				$this->seek($Offsets[$i]);
				$GSLookup[$i]['Type'] = $this->read_ushort();
				$GSLookup[$i]['Flag'] = $flag = $this->read_ushort();
				$GSLookup[$i]['SubtableCount'] = $SubtableCount[$i] = $this->read_ushort();
				for ($c = 0; $c < $SubtableCount[$i]; $c++) {
					$GSLookup[$i]['Subtables'][$c] = $Offsets[$i] + $this->read_ushort();
				}
				// MarkFilteringSet = Index (base 0) into GDEF mark glyph sets structure
				if (($flag & 0x0010) == 0x0010) {
					$GSLookup[$i]['MarkFilteringSet'] = $this->read_ushort();
				} else {
					$GSLookup[$i]['MarkFilteringSet'] = '';
				}

				// Lookup Type 7: Extension
				if ($GSLookup[$i]['Type'] == 7) {
					// Overwrites new offset (32-bit) for each subtable, and a new lookup Type
					for ($c = 0; $c < $SubtableCount[$i]; $c++) {
						$this->seek($GSLookup[$i]['Subtables'][$c]);
						$ExtensionPosFormat = $this->read_ushort();
						$type = $this->read_ushort();
						$ext_offset = $this->read_ulong();
						$GSLookup[$i]['Subtables'][$c] = $GSLookup[$i]['Subtables'][$c] + $ext_offset;
					}
					$GSLookup[$i]['Type'] = $type;
				}
			}

//print_r($GSLookup); exit;
			//=====================================================================================
			// Process Whole LookupList - Get LuCoverage = Lookup coverage just for first glyph
			$this->GSLuCoverage = array();
			for ($i = 0; $i < $LookupCount; $i++) {
				for ($c = 0; $c < $GSLookup[$i]['SubtableCount']; $c++) {

					$this->seek($GSLookup[$i]['Subtables'][$c]);
					$PosFormat = $this->read_ushort();

					if ($GSLookup[$i]['Type'] == 5 && $PosFormat == 3) {
						$this->skip(4);
					} else if ($GSLookup[$i]['Type'] == 6 && $PosFormat == 3) {
						$BacktrackGlyphCount = $this->read_ushort();
						$this->skip(2 * $BacktrackGlyphCount + 2);
					}
					// NB Coverage only looks at glyphs for position 1 (i.e. 5.3 and 6.3)	// NEEDS TO READ ALL ********************
					$Coverage = $GSLookup[$i]['Subtables'][$c] + $this->read_ushort();
					$this->seek($Coverage);
					$glyphs = $this->_getCoverage(false, 2);
					$this->GSLuCoverage[$i][$c] = $glyphs;
				}
			}

// $this->GSLuCoverage and $GSLookup
			//=====================================================================================
			//=====================================================================================
			$s = '<?php
$GSLuCoverage = ' . var_export($this->GSLuCoverage, true) . ';
?>';

			file_put_contents(_MPDF_TTFONTDATAPATH . $this->fontkey . '.GSUBdata.php', $s);

			//=====================================================================================
			//=====================================================================================
			//=====================================================================================
			//=====================================================================================
// Now repeats as original to get Substitution rules
			//=====================================================================================
			//=====================================================================================
			//=====================================================================================
			// Get metadata and offsets for whole Lookup List table
			$this->seek($LookupList_offset);
			$LookupCount = $this->read_ushort();
			$Lookup = array();
			for ($i = 0; $i < $LookupCount; $i++) {
				$Lookup[$i]['offset'] = $LookupList_offset + $this->read_ushort();
			}
			for ($i = 0; $i < $LookupCount; $i++) {
				$this->seek($Lookup[$i]['offset']);
				$Lookup[$i]['Type'] = $this->read_ushort();
				$Lookup[$i]['Flag'] = $flag = $this->read_ushort();
				$Lookup[$i]['SubtableCount'] = $this->read_ushort();
				for ($c = 0; $c < $Lookup[$i]['SubtableCount']; $c++) {
					$Lookup[$i]['Subtable'][$c]['Offset'] = $Lookup[$i]['offset'] + $this->read_ushort();
				}
				// MarkFilteringSet = Index (base 0) into GDEF mark glyph sets structure
				if (($flag & 0x0010) == 0x0010) {
					$Lookup[$i]['MarkFilteringSet'] = $this->read_ushort();
				} else {
					$Lookup[$i]['MarkFilteringSet'] = '';
				}

				// Lookup Type 7: Extension
				if ($Lookup[$i]['Type'] == 7) {
					// Overwrites new offset (32-bit) for each subtable, and a new lookup Type
					for ($c = 0; $c < $Lookup[$i]['SubtableCount']; $c++) {
						$this->seek($Lookup[$i]['Subtable'][$c]['Offset']);
						$ExtensionPosFormat = $this->read_ushort();
						$type = $this->read_ushort();
						$Lookup[$i]['Subtable'][$c]['Offset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ulong();
					}
					$Lookup[$i]['Type'] = $type;
				}
			}

//print_r($Lookup); exit;
			//=====================================================================================
			// Process (1) Whole LookupList
			for ($i = 0; $i < $LookupCount; $i++) {
				for ($c = 0; $c < $Lookup[$i]['SubtableCount']; $c++) {

					$this->seek($Lookup[$i]['Subtable'][$c]['Offset']);
					$SubstFormat = $this->read_ushort();
					$Lookup[$i]['Subtable'][$c]['Format'] = $SubstFormat;

					/*
					  Lookup['Type'] Enumeration table for glyph substitution
					  Value	Type	Description
					  1	Single	Replace one glyph with one glyph
					  2	Multiple	Replace one glyph with more than one glyph
					  3	Alternate	Replace one glyph with one of many glyphs
					  4	Ligature	Replace multiple glyphs with one glyph
					  5	Context	Replace one or more glyphs in context
					  6	Chaining Context	Replace one or more glyphs in chained context
					  7	Extension Substitution	Extension mechanism for other substitutions (i.e. this excludes the Extension type substitution itself)
					  8	Reverse chaining context single 	Applied in reverse order, replace single glyph in chaining context
					 */

					// LookupType 1: Single Substitution Subtable
					if ($Lookup[$i]['Type'] == 1) {
						$Lookup[$i]['Subtable'][$c]['CoverageTableOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
						if ($SubstFormat == 1) { // Calculated output glyph indices
							$Lookup[$i]['Subtable'][$c]['DeltaGlyphID'] = $this->read_short();
						} else if ($SubstFormat == 2) { // Specified output glyph indices
							$GlyphCount = $this->read_ushort();
							for ($g = 0; $g < $GlyphCount; $g++) {
								$Lookup[$i]['Subtable'][$c]['Glyphs'][] = $this->read_ushort();
							}
						}
					}
					// LookupType 2: Multiple Substitution Subtable
					else if ($Lookup[$i]['Type'] == 2) {
						$Lookup[$i]['Subtable'][$c]['CoverageTableOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
						$Lookup[$i]['Subtable'][$c]['SequenceCount'] = $SequenceCount = $this->read_short();
						for ($s = 0; $s < $SequenceCount; $s++) {
							$Lookup[$i]['Subtable'][$c]['Sequences'][$s]['Offset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_short();
						}
						for ($s = 0; $s < $SequenceCount; $s++) {
							// Sequence Tables
							$this->seek($Lookup[$i]['Subtable'][$c]['Sequences'][$s]['Offset']);
							$Lookup[$i]['Subtable'][$c]['Sequences'][$s]['GlyphCount'] = $this->read_short();
							for ($g = 0; $g < $Lookup[$i]['Subtable'][$c]['Sequences'][$s]['GlyphCount']; $g++) {
								$Lookup[$i]['Subtable'][$c]['Sequences'][$s]['SubstituteGlyphID'][] = $this->read_ushort();
							}
						}
					}
					// LookupType 3: Alternate Forms
					else if ($Lookup[$i]['Type'] == 3) {
						$Lookup[$i]['Subtable'][$c]['CoverageTableOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
						$Lookup[$i]['Subtable'][$c]['AlternateSetCount'] = $AlternateSetCount = $this->read_short();
						for ($s = 0; $s < $AlternateSetCount; $s++) {
							$Lookup[$i]['Subtable'][$c]['AlternateSets'][$s]['Offset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_short();
						}

						for ($s = 0; $s < $AlternateSetCount; $s++) {
							// AlternateSet Tables
							$this->seek($Lookup[$i]['Subtable'][$c]['AlternateSets'][$s]['Offset']);
							$Lookup[$i]['Subtable'][$c]['AlternateSets'][$s]['GlyphCount'] = $this->read_short();
							for ($g = 0; $g < $Lookup[$i]['Subtable'][$c]['AlternateSets'][$s]['GlyphCount']; $g++) {
								$Lookup[$i]['Subtable'][$c]['AlternateSets'][$s]['SubstituteGlyphID'][] = $this->read_ushort();
							}
						}
					}
					// LookupType 4: Ligature Substitution Subtable
					else if ($Lookup[$i]['Type'] == 4) {
						$Lookup[$i]['Subtable'][$c]['CoverageTableOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
						$Lookup[$i]['Subtable'][$c]['LigSetCount'] = $LigSetCount = $this->read_short();
						for ($s = 0; $s < $LigSetCount; $s++) {
							$Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Offset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_short();
						}
						for ($s = 0; $s < $LigSetCount; $s++) {
							// LigatureSet Tables
							$this->seek($Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Offset']);
							$Lookup[$i]['Subtable'][$c]['LigSet'][$s]['LigCount'] = $this->read_short();
							for ($g = 0; $g < $Lookup[$i]['Subtable'][$c]['LigSet'][$s]['LigCount']; $g++) {
								$Lookup[$i]['Subtable'][$c]['LigSet'][$s]['LigatureOffset'][$g] = $Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Offset'] + $this->read_ushort();
							}
						}
						for ($s = 0; $s < $LigSetCount; $s++) {
							for ($g = 0; $g < $Lookup[$i]['Subtable'][$c]['LigSet'][$s]['LigCount']; $g++) {
								// Ligature tables
								$this->seek($Lookup[$i]['Subtable'][$c]['LigSet'][$s]['LigatureOffset'][$g]);
								$Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Ligature'][$g]['LigGlyph'] = $this->read_ushort();
								$Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Ligature'][$g]['CompCount'] = $this->read_ushort();
								for ($l = 1; $l < $Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Ligature'][$g]['CompCount']; $l++) {
									$Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Ligature'][$g]['GlyphID'][$l] = $this->read_ushort();
								}
							}
						}
					}

					// LookupType 5: Contextual Substitution Subtable
					else if ($Lookup[$i]['Type'] == 5) {
						// Format 1: Context Substitution
						if ($SubstFormat == 1) {
							$Lookup[$i]['Subtable'][$c]['CoverageTableOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							$Lookup[$i]['Subtable'][$c]['SubRuleSetCount'] = $SubRuleSetCount = $this->read_short();
							for ($s = 0; $s < $SubRuleSetCount; $s++) {
								$Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['Offset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_short();
							}
							for ($s = 0; $s < $SubRuleSetCount; $s++) {
								// SubRuleSet Tables
								$this->seek($Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['Offset']);
								$Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRuleCount'] = $this->read_short();
								for ($g = 0; $g < $Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRuleCount']; $g++) {
									$Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRuleOffset'][$g] = $Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['Offset'] + $this->read_ushort();
								}
							}
							for ($s = 0; $s < $SubRuleSetCount; $s++) {
								// SubRule Tables
								for ($g = 0; $g < $Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRuleCount']; $g++) {
									// Ligature tables
									$this->seek($Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRuleOffset'][$g]);

									$Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRule'][$g]['GlyphCount'] = $this->read_ushort();
									$Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRule'][$g]['SubstCount'] = $this->read_ushort();
									// "Input"::[GlyphCount - 1]::Array of input GlyphIDs-start with second glyph
									for ($l = 1; $l < $Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRule'][$g]['GlyphCount']; $l++) {
										$Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRule'][$g]['Input'][$l] = $this->read_ushort();
									}
									// "SubstLookupRecord"::[SubstCount]::Array of SubstLookupRecords-in design order
									for ($l = 0; $l < $Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRule'][$g]['SubstCount']; $l++) {
										$Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRule'][$g]['SubstLookupRecord'][$l]['SequenceIndex'] = $this->read_ushort();
										$Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRule'][$g]['SubstLookupRecord'][$l]['LookupListIndex'] = $this->read_ushort();
									}
								}
							}
						}
						// Format 2: Class-based Context Glyph Substitution
						else if ($SubstFormat == 2) {
							$Lookup[$i]['Subtable'][$c]['CoverageTableOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							$Lookup[$i]['Subtable'][$c]['ClassDefOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							$Lookup[$i]['Subtable'][$c]['SubClassSetCnt'] = $this->read_ushort();
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['SubClassSetCnt']; $b++) {
								$offset = $this->read_ushort();
								if ($offset == 0x0000) {
									$Lookup[$i]['Subtable'][$c]['SubClassSetOffset'][] = 0;
								} else {
									$Lookup[$i]['Subtable'][$c]['SubClassSetOffset'][] = $Lookup[$i]['Subtable'][$c]['Offset'] + $offset;
								}
							}
						} else {
							throw new MpdfException("GPOS Lookup Type " . $Lookup[$i]['Type'] . ", Format " . $SubstFormat . " not supported (ttfontsuni.php).");
						}
					}

					// LookupType 6: Chaining Contextual Substitution Subtable
					else if ($Lookup[$i]['Type'] == 6) {
						// Format 1: Simple Chaining Context Glyph Substitution  p255
						if ($SubstFormat == 1) {
							$Lookup[$i]['Subtable'][$c]['CoverageTableOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							$Lookup[$i]['Subtable'][$c]['ChainSubRuleSetCount'] = $this->read_ushort();
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['ChainSubRuleSetCount']; $b++) {
								$Lookup[$i]['Subtable'][$c]['ChainSubRuleSetOffset'][] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							}
						}
						// Format 2: Class-based Chaining Context Glyph Substitution  p257
						else if ($SubstFormat == 2) {
							$Lookup[$i]['Subtable'][$c]['CoverageTableOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							$Lookup[$i]['Subtable'][$c]['BacktrackClassDefOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							$Lookup[$i]['Subtable'][$c]['InputClassDefOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							$Lookup[$i]['Subtable'][$c]['LookaheadClassDefOffset'] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							$Lookup[$i]['Subtable'][$c]['ChainSubClassSetCnt'] = $this->read_ushort();
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['ChainSubClassSetCnt']; $b++) {
								$offset = $this->read_ushort();
								if ($offset == 0x0000) {
									$Lookup[$i]['Subtable'][$c]['ChainSubClassSetOffset'][] = $offset;
								} else {
									$Lookup[$i]['Subtable'][$c]['ChainSubClassSetOffset'][] = $Lookup[$i]['Subtable'][$c]['Offset'] + $offset;
								}
							}
						}
						// Format 3: Coverage-based Chaining Context Glyph Substitution  p259
						else if ($SubstFormat == 3) {
							$Lookup[$i]['Subtable'][$c]['BacktrackGlyphCount'] = $this->read_ushort();
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['BacktrackGlyphCount']; $b++) {
								$Lookup[$i]['Subtable'][$c]['CoverageBacktrack'][] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							}
							$Lookup[$i]['Subtable'][$c]['InputGlyphCount'] = $this->read_ushort();
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['InputGlyphCount']; $b++) {
								$Lookup[$i]['Subtable'][$c]['CoverageInput'][] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							}
							$Lookup[$i]['Subtable'][$c]['LookaheadGlyphCount'] = $this->read_ushort();
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['LookaheadGlyphCount']; $b++) {
								$Lookup[$i]['Subtable'][$c]['CoverageLookahead'][] = $Lookup[$i]['Subtable'][$c]['Offset'] + $this->read_ushort();
							}
							$Lookup[$i]['Subtable'][$c]['SubstCount'] = $this->read_ushort();
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['SubstCount']; $b++) {
								$Lookup[$i]['Subtable'][$c]['SubstLookupRecord'][$b]['SequenceIndex'] = $this->read_ushort();
								$Lookup[$i]['Subtable'][$c]['SubstLookupRecord'][$b]['LookupListIndex'] = $this->read_ushort();
								/*
								  Substitution Lookup Record
								  All contextual substitution subtables specify the substitution data in a Substitution Lookup Record (SubstLookupRecord). Each record contains a SequenceIndex, which indicates the position where the substitution will occur in the glyph sequence. In addition, a LookupListIndex identifies the lookup to be applied at the glyph position specified by the SequenceIndex.
								 */
							}
						}
					} else {
						throw new MpdfException("Lookup Type " . $Lookup[$i]['Type'] . " not supported.");
					}
				}
			}
//print_r($Lookup); exit;
			//=====================================================================================
			// Process (2) Whole LookupList
			// Get Coverage tables and prepare preg_replace
			for ($i = 0; $i < $LookupCount; $i++) {
				for ($c = 0; $c < $Lookup[$i]['SubtableCount']; $c++) {
					$SubstFormat = $Lookup[$i]['Subtable'][$c]['Format'];

					// LookupType 1: Single Substitution Subtable 1 => 1
					if ($Lookup[$i]['Type'] == 1) {
						$this->seek($Lookup[$i]['Subtable'][$c]['CoverageTableOffset']);
						$glyphs = $this->_getCoverage(false);
						for ($g = 0; $g < count($glyphs); $g++) {
							$replace = array();
							$substitute = array();
							$replace[] = unicode_hex($this->glyphToChar[$glyphs[$g]][0]);
							// Flag = Ignore
							if ($this->_checkGSUBignore($Lookup[$i]['Flag'], $replace[0], $Lookup[$i]['MarkFilteringSet'])) {
								continue;
							}
							if (isset($Lookup[$i]['Subtable'][$c]['DeltaGlyphID'])) { // Format 1
								$substitute[] = unicode_hex($this->glyphToChar[($glyphs[$g] + $Lookup[$i]['Subtable'][$c]['DeltaGlyphID'])][0]);
							} else { // Format 2
								$substitute[] = unicode_hex($this->glyphToChar[($Lookup[$i]['Subtable'][$c]['Glyphs'][$g])][0]);
							}
							$Lookup[$i]['Subtable'][$c]['subs'][] = array('Replace' => $replace, 'substitute' => $substitute);
						}
					}

					// LookupType 2: Multiple Substitution Subtable 1 => n
					else if ($Lookup[$i]['Type'] == 2) {
						$this->seek($Lookup[$i]['Subtable'][$c]['CoverageTableOffset']);
						$glyphs = $this->_getCoverage();
						for ($g = 0; $g < count($glyphs); $g++) {
							$replace = array();
							$substitute = array();
							$replace[] = $glyphs[$g];
							// Flag = Ignore
							if ($this->_checkGSUBignore($Lookup[$i]['Flag'], $replace[0], $Lookup[$i]['MarkFilteringSet'])) {
								continue;
							}
							if (!isset($Lookup[$i]['Subtable'][$c]['Sequences'][$g]['SubstituteGlyphID']) || count($Lookup[$i]['Subtable'][$c]['Sequences'][$g]['SubstituteGlyphID']) == 0) {
								continue;
							} // Illegal for GlyphCount to be 0; either error in font, or something has gone wrong - lets carry on for now!
							foreach ($Lookup[$i]['Subtable'][$c]['Sequences'][$g]['SubstituteGlyphID'] AS $sub) {
								$substitute[] = unicode_hex($this->glyphToChar[$sub][0]);
							}
							$Lookup[$i]['Subtable'][$c]['subs'][] = array('Replace' => $replace, 'substitute' => $substitute);
						}
					}
					// LookupType 3: Alternate Forms 1 => 1 (only first alternate form is used)
					else if ($Lookup[$i]['Type'] == 3) {
						$this->seek($Lookup[$i]['Subtable'][$c]['CoverageTableOffset']);
						$glyphs = $this->_getCoverage();
						for ($g = 0; $g < count($glyphs); $g++) {
							$replace = array();
							$substitute = array();
							$replace[] = $glyphs[$g];
							// Flag = Ignore
							if ($this->_checkGSUBignore($Lookup[$i]['Flag'], $replace[0], $Lookup[$i]['MarkFilteringSet'])) {
								continue;
							}
							$gid = $Lookup[$i]['Subtable'][$c]['AlternateSets'][$g]['SubstituteGlyphID'][0];
							if (!isset($this->glyphToChar[$gid][0])) {
								continue;
							}
							$substitute[] = unicode_hex($this->glyphToChar[$gid][0]);
							$Lookup[$i]['Subtable'][$c]['subs'][] = array('Replace' => $replace, 'substitute' => $substitute);
						}
					}
					// LookupType 4: Ligature Substitution Subtable n => 1
					else if ($Lookup[$i]['Type'] == 4) {
						$this->seek($Lookup[$i]['Subtable'][$c]['CoverageTableOffset']);
						$glyphs = $this->_getCoverage();
						$LigSetCount = $Lookup[$i]['Subtable'][$c]['LigSetCount'];
						for ($s = 0; $s < $LigSetCount; $s++) {
							for ($g = 0; $g < $Lookup[$i]['Subtable'][$c]['LigSet'][$s]['LigCount']; $g++) {
								$replace = array();
								$substitute = array();
								$replace[] = $glyphs[$s];
								// Flag = Ignore
								if ($this->_checkGSUBignore($Lookup[$i]['Flag'], $replace[0], $Lookup[$i]['MarkFilteringSet'])) {
									continue;
								}
								for ($l = 1; $l < $Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Ligature'][$g]['CompCount']; $l++) {
									$gid = $Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Ligature'][$g]['GlyphID'][$l];
									$rpl = unicode_hex($this->glyphToChar[$gid][0]);
									// Flag = Ignore
									if ($this->_checkGSUBignore($Lookup[$i]['Flag'], $rpl, $Lookup[$i]['MarkFilteringSet'])) {
										continue 2;
									}
									$replace[] = $rpl;
								}
								$gid = $Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Ligature'][$g]['LigGlyph'];
								if (!isset($this->glyphToChar[$gid][0])) {
									continue;
								}
								$substitute[] = unicode_hex($this->glyphToChar[$gid][0]);
								$Lookup[$i]['Subtable'][$c]['subs'][] = array('Replace' => $replace, 'substitute' => $substitute, 'CompCount' => $Lookup[$i]['Subtable'][$c]['LigSet'][$s]['Ligature'][$g]['CompCount']);
							}
						}
					}

					// LookupType 5: Contextual Substitution Subtable
					else if ($Lookup[$i]['Type'] == 5) {
						// Format 1: Context Substitution
						if ($SubstFormat == 1) {
							$this->seek($Lookup[$i]['Subtable'][$c]['CoverageTableOffset']);
							$Lookup[$i]['Subtable'][$c]['CoverageGlyphs'] = $CoverageGlyphs = $this->_getCoverage();

							for ($s = 0; $s < $Lookup[$i]['Subtable'][$c]['SubRuleSetCount']; $s++) {
								$SubRuleSet = $Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s];
								$Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['FirstGlyph'] = $CoverageGlyphs[$s];
								for ($r = 0; $r < $Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRuleCount']; $r++) {
									$GlyphCount = $Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRule'][$r]['GlyphCount'];
									for ($g = 1; $g < $GlyphCount; $g++) {
										$glyphID = $Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRule'][$r]['Input'][$g];
										$Lookup[$i]['Subtable'][$c]['SubRuleSet'][$s]['SubRule'][$r]['InputGlyphs'][$g] = unicode_hex($this->glyphToChar[$glyphID][0]);
									}
								}
							}
						}
						// Format 2: Class-based Context Glyph Substitution
						else if ($SubstFormat == 2) {
							$this->seek($Lookup[$i]['Subtable'][$c]['CoverageTableOffset']);
							$Lookup[$i]['Subtable'][$c]['CoverageGlyphs'] = $CoverageGlyphs = $this->_getCoverage();

							$InputClasses = $this->_getClasses($Lookup[$i]['Subtable'][$c]['ClassDefOffset']);
							$Lookup[$i]['Subtable'][$c]['InputClasses'] = $InputClasses;
							for ($s = 0; $s < $Lookup[$i]['Subtable'][$c]['SubClassSetCnt']; $s++) {
								if ($Lookup[$i]['Subtable'][$c]['SubClassSetOffset'][$s] > 0) {
									$this->seek($Lookup[$i]['Subtable'][$c]['SubClassSetOffset'][$s]);
									$Lookup[$i]['Subtable'][$c]['SubClassSet'][$s]['SubClassRuleCnt'] = $SubClassRuleCnt = $this->read_ushort();
									$SubClassRule = array();
									for ($b = 0; $b < $SubClassRuleCnt; $b++) {
										$SubClassRule[$b] = $Lookup[$i]['Subtable'][$c]['SubClassSetOffset'][$s] + $this->read_ushort();
										$Lookup[$i]['Subtable'][$c]['SubClassSet'][$s]['SubClassRule'][$b] = $SubClassRule[$b];
									}
								}
							}

							for ($s = 0; $s < $Lookup[$i]['Subtable'][$c]['SubClassSetCnt']; $s++) {
								if ($Lookup[$i]['Subtable'][$c]['SubClassSetOffset'][$s] > 0) {
									$SubClassRuleCnt = $Lookup[$i]['Subtable'][$c]['SubClassSet'][$s]['SubClassRuleCnt'];
									for ($b = 0; $b < $SubClassRuleCnt; $b++) {
										$this->seek($Lookup[$i]['Subtable'][$c]['SubClassSet'][$s]['SubClassRule'][$b]);
										$Rule = array();
										$Rule['InputGlyphCount'] = $this->read_ushort();
										$Rule['SubstCount'] = $this->read_ushort();
										for ($r = 1; $r < $Rule['InputGlyphCount']; $r++) {
											$Rule['Input'][$r] = $this->read_ushort();
										}
										for ($r = 0; $r < $Rule['SubstCount']; $r++) {
											$Rule['SequenceIndex'][$r] = $this->read_ushort();
											$Rule['LookupListIndex'][$r] = $this->read_ushort();
										}

										$Lookup[$i]['Subtable'][$c]['SubClassSet'][$s]['SubClassRule'][$b] = $Rule;
									}
								}
							}
						}
						// Format 3: Coverage-based Context Glyph Substitution
						else if ($SubstFormat == 3) {
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['InputGlyphCount']; $b++) {
								$this->seek($Lookup[$i]['Subtable'][$c]['CoverageInput'][$b]);
								$glyphs = $this->_getCoverage();
								$Lookup[$i]['Subtable'][$c]['CoverageInputGlyphs'][] = implode("|", $glyphs);
							}
							throw new MpdfException("Lookup Type 5, SubstFormat 3 not tested. Please report this with the name of font used - " . $this->fontkey);
						}
					}

					// LookupType 6: Chaining Contextual Substitution Subtable
					else if ($Lookup[$i]['Type'] == 6) {
						// Format 1: Simple Chaining Context Glyph Substitution  p255
						if ($SubstFormat == 1) {
							$this->seek($Lookup[$i]['Subtable'][$c]['CoverageTableOffset']);
							$Lookup[$i]['Subtable'][$c]['CoverageGlyphs'] = $CoverageGlyphs = $this->_getCoverage();

							$ChainSubRuleSetCnt = $Lookup[$i]['Subtable'][$c]['ChainSubRuleSetCount'];

							for ($s = 0; $s < $ChainSubRuleSetCnt; $s++) {
								$this->seek($Lookup[$i]['Subtable'][$c]['ChainSubRuleSetOffset'][$s]);
								$ChainSubRuleCnt = $Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRuleCount'] = $this->read_ushort();
								for ($r = 0; $r < $ChainSubRuleCnt; $r++) {
									$Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRuleOffset'][$r] = $Lookup[$i]['Subtable'][$c]['ChainSubRuleSetOffset'][$s] + $this->read_ushort();
								}
							}
							for ($s = 0; $s < $ChainSubRuleSetCnt; $s++) {
								$ChainSubRuleCnt = $Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRuleCount'];
								for ($r = 0; $r < $ChainSubRuleCnt; $r++) {
									// ChainSubRule
									$this->seek($Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRuleOffset'][$r]);

									$BacktrackGlyphCount = $Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRule'][$r]['BacktrackGlyphCount'] = $this->read_ushort();
									for ($g = 0; $g < $BacktrackGlyphCount; $g++) {
										$glyphID = $this->read_ushort();
										$Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRule'][$r]['BacktrackGlyphs'][$g] = unicode_hex($this->glyphToChar[$glyphID][0]);
									}

									$InputGlyphCount = $Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRule'][$r]['InputGlyphCount'] = $this->read_ushort();
									for ($g = 1; $g < $InputGlyphCount; $g++) {
										$glyphID = $this->read_ushort();
										$Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRule'][$r]['InputGlyphs'][$g] = unicode_hex($this->glyphToChar[$glyphID][0]);
									}


									$LookaheadGlyphCount = $Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRule'][$r]['LookaheadGlyphCount'] = $this->read_ushort();
									for ($g = 0; $g < $LookaheadGlyphCount; $g++) {
										$glyphID = $this->read_ushort();
										$Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRule'][$r]['LookaheadGlyphs'][$g] = unicode_hex($this->glyphToChar[$glyphID][0]);
									}

									$SubstCount = $Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRule'][$r]['SubstCount'] = $this->read_ushort();
									for ($lu = 0; $lu < $SubstCount; $lu++) {
										$Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRule'][$r]['SequenceIndex'][$lu] = $this->read_ushort();
										$Lookup[$i]['Subtable'][$c]['ChainSubRuleSet'][$s]['ChainSubRule'][$r]['LookupListIndex'][$lu] = $this->read_ushort();
									}
								}
							}
						}
						// Format 2: Class-based Chaining Context Glyph Substitution  p257
						else if ($SubstFormat == 2) {
							$this->seek($Lookup[$i]['Subtable'][$c]['CoverageTableOffset']);
							$Lookup[$i]['Subtable'][$c]['CoverageGlyphs'] = $CoverageGlyphs = $this->_getCoverage();

							$BacktrackClasses = $this->_getClasses($Lookup[$i]['Subtable'][$c]['BacktrackClassDefOffset']);
							$Lookup[$i]['Subtable'][$c]['BacktrackClasses'] = $BacktrackClasses;

							$InputClasses = $this->_getClasses($Lookup[$i]['Subtable'][$c]['InputClassDefOffset']);
							$Lookup[$i]['Subtable'][$c]['InputClasses'] = $InputClasses;

							$LookaheadClasses = $this->_getClasses($Lookup[$i]['Subtable'][$c]['LookaheadClassDefOffset']);
							$Lookup[$i]['Subtable'][$c]['LookaheadClasses'] = $LookaheadClasses;

							for ($s = 0; $s < $Lookup[$i]['Subtable'][$c]['ChainSubClassSetCnt']; $s++) {
								if ($Lookup[$i]['Subtable'][$c]['ChainSubClassSetOffset'][$s] > 0) {
									$this->seek($Lookup[$i]['Subtable'][$c]['ChainSubClassSetOffset'][$s]);
									$Lookup[$i]['Subtable'][$c]['ChainSubClassSet'][$s]['ChainSubClassRuleCnt'] = $ChainSubClassRuleCnt = $this->read_ushort();
									$ChainSubClassRule = array();
									for ($b = 0; $b < $ChainSubClassRuleCnt; $b++) {
										$ChainSubClassRule[$b] = $Lookup[$i]['Subtable'][$c]['ChainSubClassSetOffset'][$s] + $this->read_ushort();
										$Lookup[$i]['Subtable'][$c]['ChainSubClassSet'][$s]['ChainSubClassRule'][$b] = $ChainSubClassRule[$b];
									}
								}
							}

							for ($s = 0; $s < $Lookup[$i]['Subtable'][$c]['ChainSubClassSetCnt']; $s++) {
								if (isset($Lookup[$i]['Subtable'][$c]['ChainSubClassSet'][$s]['ChainSubClassRuleCnt'])) {
									$ChainSubClassRuleCnt = $Lookup[$i]['Subtable'][$c]['ChainSubClassSet'][$s]['ChainSubClassRuleCnt'];
								} else {
									$ChainSubClassRuleCnt = 0;
								}
								for ($b = 0; $b < $ChainSubClassRuleCnt; $b++) {
									if ($Lookup[$i]['Subtable'][$c]['ChainSubClassSetOffset'][$s] > 0) {
										$this->seek($Lookup[$i]['Subtable'][$c]['ChainSubClassSet'][$s]['ChainSubClassRule'][$b]);
										$Rule = array();
										$Rule['BacktrackGlyphCount'] = $this->read_ushort();
										for ($r = 0; $r < $Rule['BacktrackGlyphCount']; $r++) {
											$Rule['Backtrack'][$r] = $this->read_ushort();
										}
										$Rule['InputGlyphCount'] = $this->read_ushort();
										for ($r = 1; $r < $Rule['InputGlyphCount']; $r++) {
											$Rule['Input'][$r] = $this->read_ushort();
										}
										$Rule['LookaheadGlyphCount'] = $this->read_ushort();
										for ($r = 0; $r < $Rule['LookaheadGlyphCount']; $r++) {
											$Rule['Lookahead'][$r] = $this->read_ushort();
										}
										$Rule['SubstCount'] = $this->read_ushort();
										for ($r = 0; $r < $Rule['SubstCount']; $r++) {
											$Rule['SequenceIndex'][$r] = $this->read_ushort();
											$Rule['LookupListIndex'][$r] = $this->read_ushort();
										}

										$Lookup[$i]['Subtable'][$c]['ChainSubClassSet'][$s]['ChainSubClassRule'][$b] = $Rule;
									}
								}
							}
						}
						// Format 3: Coverage-based Chaining Context Glyph Substitution  p259
						else if ($SubstFormat == 3) {
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['BacktrackGlyphCount']; $b++) {
								$this->seek($Lookup[$i]['Subtable'][$c]['CoverageBacktrack'][$b]);
								$glyphs = $this->_getCoverage();
								$Lookup[$i]['Subtable'][$c]['CoverageBacktrackGlyphs'][] = implode("|", $glyphs);
							}
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['InputGlyphCount']; $b++) {
								$this->seek($Lookup[$i]['Subtable'][$c]['CoverageInput'][$b]);
								$glyphs = $this->_getCoverage();
								$Lookup[$i]['Subtable'][$c]['CoverageInputGlyphs'][] = implode("|", $glyphs);
								// Don't use above value as these are ordered numerically not as need to process
							}
							for ($b = 0; $b < $Lookup[$i]['Subtable'][$c]['LookaheadGlyphCount']; $b++) {
								$this->seek($Lookup[$i]['Subtable'][$c]['CoverageLookahead'][$b]);
								$glyphs = $this->_getCoverage();
								$Lookup[$i]['Subtable'][$c]['CoverageLookaheadGlyphs'][] = implode("|", $glyphs);
							}
						}
					}
				}
			}


			//=====================================================================================
			//=====================================================================================
			//=====================================================================================
			//=====================================================================================

			$GSUBScriptLang = array();
			$rtlpua = array(); // All glyphs added to PUA [for magic_reverse]
			foreach ($gsub AS $st => $scripts) {
				foreach ($scripts AS $t => $langsys) {
					$lul = array(); // array of LookupListIndexes
					$tags = array(); // corresponding array of feature tags e.g. 'ccmp'
//print_r($langsys ); exit;
					foreach ($langsys AS $tag => $ft) {
						foreach ($ft AS $ll) {
							$lul[$ll] = $tag;
						}
					}
					ksort($lul); // Order the Lookups in the order they are in the GUSB table, regardless of Feature order
					$volt = $this->_getGSUBarray($Lookup, $lul, $st);
//print_r($lul); exit;
					//=====================================================================================
					//=====================================================================================
					// Interrogate $volt
					// isol, fin, medi, init(arab syrc) into $rtlSUB for use in ArabJoin
					// but also identify all RTL chars in PUA for magic_reverse (arab syrc hebr thaa nko  samr)
					// identify reph, matras, vatu, half forms etc for Indic for final re-ordering
					//=====================================================================================
					//=====================================================================================
					$rtl = array();
					$rtlSUB = "array()";
					$finals = '';
					if (strpos('arab syrc hebr thaa nko  samr', $st) !== false) { // all RTL scripts [any/all languages] ? Mandaic
//print_r($volt); exit;
						foreach ($volt AS $v) {
							// isol fina fin2 fin3 medi med2 for Syriac
							// ISOLATED FORM :: FINAL :: INITIAL :: MEDIAL :: MED2 :: FIN2 :: FIN3
							if (strpos('isol fina init medi fin2 fin3 med2', $v['tag']) !== false) {
								$key = $v['match'];
								$key = preg_replace('/[\(\)]*/', '', $key);
								$sub = $v['replace'];
								if ($v['tag'] == 'isol')
									$kk = 0;
								else if ($v['tag'] == 'fina')
									$kk = 1;
								else if ($v['tag'] == 'init')
									$kk = 2;
								else if ($v['tag'] == 'medi')
									$kk = 3;
								else if ($v['tag'] == 'med2')
									$kk = 4;
								else if ($v['tag'] == 'fin2')
									$kk = 5;
								else if ($v['tag'] == 'fin3')
									$kk = 6;
								$rtl[$key][$kk] = $sub;
								if (isset($v['prel']) && count($v['prel']))
									$rtl[$key]['prel'][$kk] = $v['prel'];
								if (isset($v['postl']) && count($v['postl']))
									$rtl[$key]['postl'][$kk] = $v['postl'];
								if (isset($v['ignore']) && $v['ignore']) {
									$rtl[$key]['ignore'][$kk] = $v['ignore'];
								}
								$rtlpua[] = $sub;
							}
							// Add any other glyphs which are in PUA
							else {
								if (isset($v['context']) && $v['context']) {
									foreach ($v['rules'] AS $vs) {
										for ($i = 0; $i < count($vs['match']); $i++) {
											if (isset($vs['replace'][$i]) && preg_match('/^0[A-F0-9]{4}$/', $vs['match'][$i])) {
												if (preg_match('/^0[EF][A-F0-9]{3}$/', $vs['replace'][$i])) {
													$rtlpua[] = $vs['replace'][$i];
												}
											}
										}
									}
								} else {
									preg_match_all('/\((0[A-F0-9]{4})\)/', $v['match'], $m);
									for ($i = 0; $i < count($m[0]); $i++) {
										$sb = explode(' ', $v['replace']);
										foreach ($sb AS $sbg) {
											if (preg_match('/(0[EF][A-F0-9]{3})/', $sbg, $mr)) {
												$rtlpua[] = $mr[1];
											}
										}
									}
								}
							}
						}
//print_r($rtl); exit;
						// For kashida, need to determine all final forms except ones already identified by kashida
						// priority rules (see otl.php)
						foreach ($rtl AS $base => $variants) {
							if (isset($variants[1])) { // i.e. final form
								if (strpos('0FE8E 0FE94 0FEA2 0FEAA 0FEAE 0FEC2 0FEDA 0FEDE 0FB93 0FECA 0FED2 0FED6 0FEEE 0FEF0 0FEF2', $variants[1]) === false) { // not already included
									// This version does not exclude RA (0631) FEAE; Ya (064A)  FEF2; Alef Maqsurah (0649) FEF0 which
									// are selected in priority if connected to a medial Bah
									//if (strpos('0FE8E 0FE94 0FEA2 0FEAA 0FEC2 0FEDA 0FEDE 0FB93 0FECA 0FED2 0FED6 0FEEE', $variants[1])===false) {	// not already included
									$finals .= $variants[1] . ' ';
								}
							}
						}
//echo $finals ; exit;
//print_r($rtlpua); exit;
						ksort($rtl);
						$a = var_export($rtl, true);
						$a = preg_replace('/\\\\\\\\/', "\\", $a);
						$a = preg_replace('/\'/', '"', $a);
						$a = preg_replace('/\r/', '', $a);
						$a = preg_replace('/> \n/', '>', $a);
						$a = preg_replace('/\n  \)/', ')', $a);
						$a = preg_replace('/\n    /', ' ', $a);
						$a = preg_replace('/\[IGNORE(\d+)\]/', '".$ignore[\\1]."', $a);
						$rtlSUB = preg_replace('/[ ]+/', ' ', $a);
					}
					//=====================================================================================
					// INDIC - Dynamic properties
					//=====================================================================================
					$rphf = array();
					$half = array();
					$pref = array();
					$blwf = array();
					$pstf = array();
					if (strpos('dev2 bng2 gur2 gjr2 ory2 tml2 tel2 knd2 mlm2 deva beng guru gujr orya taml telu knda mlym', $st) !== false) { // all INDIC scripts [any/all languages]
						if (strpos('deva beng guru gujr orya taml telu knda mlym', $st) !== false) {
							$is_old_spec = true;
						} else {
							$is_old_spec = false;
						}

						// First get 'locl' substitutions (reversed!)
						$loclsubs = array();
						foreach ($volt AS $v) {
							if (strpos('locl', $v['tag']) !== false) {
								$key = $v['match'];
								$key = preg_replace('/[\(\)]*/', '', $key);
								$sub = $v['replace'];
								if ($key && strlen(trim($key)) == 5 && $sub) {
									$loclsubs[$sub] = $key;
								}
							}
						}
//if (count($loclsubs)) { print_r($loclsubs); exit; }

						foreach ($volt AS $v) {
							// <rphf> <half> <pref> <blwf> <pstf>
							// defines consonant types:
							//     Reph <rphf>
							//     Half forms <half>
							//     Pre-base-reordering forms of Ra/Rra <pref>
							//     Below-base forms <blwf>
							//     Post-base forms <pstf>
							// applied together with <locl> feature to input sequences consisting of two characters
							// This is done for each consonant
							// for <rphf> and <half>, features are applied to Consonant + Halant combinations
							// for <pref>, <blwf> and <pstf>, features are applied to Halant + Consonant combinations
							// Old version eg 'deva' <pref>, <blwf> and <pstf>, features are applied to Consonant + Halant
							// Some malformed fonts still do Consonant + Halant for these - so match both??
							// If these two glyphs form a ligature, with no additional glyphs in context
							// this means the consonant has the corresponding form
							// Currently set to cope with both
							// See also classes/otl.php

							if (strpos('rphf half pref blwf pstf', $v['tag']) !== false) {
								if (isset($v['context']) && $v['context'] && $v['nBacktrack'] == 0 && $v['nLookahead'] == 0) {
									foreach ($v['rules'] AS $vs) {
										if (count($vs['match']) == 2 && count($vs['replace']) == 1) {
											$sub = $vs['replace'][0];
											// If Halant Cons   <pref>, <blwf> and <pstf> in New version only
											if (strpos('0094D 009CD 00A4D 00ACD 00B4D 00BCD 00C4D 00CCD 00D4D', $vs['match'][0]) !== false && strpos('pref blwf pstf', $v['tag']) !== false && !$is_old_spec) {
												$key = $vs['match'][1];
												$tag = $v['tag'];
												if (isset($loclsubs[$key])) {
													$$tag[$loclsubs[$k