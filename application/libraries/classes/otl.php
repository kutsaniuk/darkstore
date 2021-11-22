<?php

require_once __DIR__ . '/../MpdfException.php';

define("_OTL_OLD_SPEC_COMPAT_1", true);

define("_DICT_NODE_TYPE_SPLIT", 0x01);
define("_DICT_NODE_TYPE_LINEAR", 0x02);
define("_DICT_INTERMEDIATE_MATCH", 0x03);
define("_DICT_FINAL_MATCH", 0x04);

class otl
{

	var $mpdf;

	var $arabLeftJoining;

	var $arabRightJoining;

	var $arabTransparentJoin;

	var $arabTransparent;

	var $GSUBdata;

	var $GPOSdata;

	var $GSUBfont;

	var $fontkey;

	var $ttfOTLdata;

	var $glyphIDtoUni;

	var $_pos;

	var $GSUB_offset;

	var $GPOS_offset;

	var $MarkAttachmentType;

	var $MarkGlyphSets;

	var $GlyphClassMarks;

	var $GlyphClassLigatures;

	var $GlyphClassBases;

	var $GlyphClassComponents;

	var $Ignores;

	var $LuCoverage;

	var $OTLdata;

	var $assocLigs;

	var $assocMarks;

	var $shaper;

	var $restrictToSyllable;

	var $lbdicts; // Line-breaking dictionaries

	var $LuDataCache;

	var $debugOTL = false;

	public function __construct(mPDF $mpdf)
	{
		$this->mpdf = $mpdf;

		$this->arabic_initialise();
		$this->current_fh = '';

		$this->lbdicts = array();
		$this->LuDataCache = array();
	}

	function applyOTL($str, $useOTL)
	{
		$this->OTLdata = array();
		if (trim($str) == '') {
			return $str;
		}
		if (!$useOTL) {
			return $str;
		}

		// 1. Load GDEF data
		//==============================
		$this->fontkey = $this->mpdf->CurrentFont['fontkey'];
		$this->glyphIDtoUni = $this->mpdf->CurrentFont['glyphIDtoUni'];
		if (!isset($this->GDEFdata[$this->fontkey])) {
			include(_MPDF_TTFONTDATAPATH . $this->fontkey . '.GDEFdata.php');
			$this->GSUB_offset = $this->GDEFdata[$this->fontkey]['GSUB_offset'] = $GSUB_offset;
			$this->GPOS_offset = $this->GDEFdata[$this->fontkey]['GPOS_offset'] = $GPOS_offset;
			$this->GSUB_length = $this->GDEFdata[$this->fontkey]['GSUB_length'] = $GSUB_length;
			$this->MarkAttachmentType = $this->GDEFdata[$this->fontkey]['MarkAttachmentType'] = $MarkAttachmentType;
			$this->MarkGlyphSets = $this->GDEFdata[$this->fontkey]['MarkGlyphSets'] = $MarkGlyphSets;
			$this->GlyphClassMarks = $this->GDEFdata[$this->fontkey]['GlyphClassMarks'] = $GlyphClassMarks;
			$this->GlyphClassLigatures = $this->GDEFdata[$this->fontkey]['GlyphClassLigatures'] = $GlyphClassLigatures;
			$this->GlyphClassComponents = $this->GDEFdata[$this->fontkey]['GlyphClassComponents'] = $GlyphClassComponents;
			$this->GlyphClassBases = $this->GDEFdata[$this->fontkey]['GlyphClassBases'] = $GlyphClassBases;
		} else {
			$this->GSUB_offset = $this->GDEFdata[$this->fontkey]['GSUB_offset'];
			$this->GPOS_offset = $this->GDEFdata[$this->fontkey]['GPOS_offset'];
			$this->GSUB_length = $this->GDEFdata[$this->fontkey]['GSUB_length'];
			$this->MarkAttachmentType = $this->GDEFdata[$this->fontkey]['MarkAttachmentType'];
			$this->MarkGlyphSets = $this->GDEFdata[$this->fontkey]['MarkGlyphSets'];
			$this->GlyphClassMarks = $this->GDEFdata[$this->fontkey]['GlyphClassMarks'];
			$this->GlyphClassLigatures = $this->GDEFdata[$this->fontkey]['GlyphClassLigatures'];
			$this->GlyphClassComponents = $this->GDEFdata[$this->fontkey]['GlyphClassComponents'];
			$this->GlyphClassBases = $this->GDEFdata[$this->fontkey]['GlyphClassBases'];
		}

		// 2. Prepare string as HEX string and Analyse character properties
		//=================================================================
		$earr = $this->mpdf->UTF8StringToArray($str, false);

		$scriptblock = 0;
		$scriptblocks = array();
		$scriptblocks[0] = 0;
		$vstr = '';
		$OTLdata = array();
		$subchunk = 0;
		$charctr = 0;
		foreach ($earr as $char) {
			$ucd_record = UCDN::get_ucd_record($char);
			$sbl = $ucd_record[6];

			// Special case - Arabic End of Ayah
			if ($char == 1757) {
				$sbl = UCDN::SCRIPT_ARABIC;
			}

			if ($sbl && $sbl != 40 && $sbl != 102) {
				if ($scriptblock == 0) {
					$scriptblock = $sbl;
					$scriptblocks[$subchunk] = $scriptblock;
				} else if ($scriptblock > 0 && $scriptblock != $sbl) {
					// *************************************************
					// NEW (non-common) Script encountered in this chunk. Start a new subchunk
					$subchunk++;
					$scriptblock = $sbl;
					$charctr = 0;
					$scriptblocks[$subchunk] = $scriptblock;
				}
			}

			$OTLdata[$subchunk][$charctr]['general_category'] = $ucd_record[0];
			$OTLdata[$subchunk][$charctr]['bidi_type'] = $ucd_record[2];

			//$OTLdata[$subchunk][$charctr]['combining_class'] = $ucd_record[1];
			//$OTLdata[$subchunk][$charctr]['bidi_type'] = $ucd_record[2];
			//$OTLdata[$subchunk][$charctr]['mirrored'] = $ucd_record[3];
			//$OTLdata[$subchunk][$charctr]['east_asian_width'] = $ucd_record[4];
			//$OTLdata[$subchunk][$charctr]['normalization_check'] = $ucd_record[5];
			//$OTLdata[$subchunk][$charctr]['script'] = $ucd_record[6];

			$charasstr = $this->unicode_hex($char);

			if (strpos($this->GlyphClassMarks, $charasstr) !== false) {
				$OTLdata[$subchunk][$charctr]['group'] = 'M';
			} else if ($char == 32 || $char == 12288) {
				$OTLdata[$subchunk][$charctr]['group'] = 'S';
			} // 12288 = 0x3000 = CJK space
			else {
				$OTLdata[$subchunk][$charctr]['group'] = 'C';
			}

			$OTLdata[$subchunk][$charctr]['uni'] = $char;
			$OTLdata[$subchunk][$charctr]['hex'] = $charasstr;
			$charctr++;
		}

		/* PROCESS EACH SUBCHUNK WITH DIFFERENT SCRIPTS */
		for ($sch = 0; $sch <= $subchunk; $sch++) {
			$this->OTLdata = $OTLdata[$sch];
			$scriptblock = $scriptblocks[$sch];

			// 3. Get Appropriate Scripts, and Shaper engine from analysing text and list of available scripts/langsys in font
			//==============================
			// Based on actual script block of text, select shaper (and line-breaking dictionaries)
			if (UCDN::SCRIPT_DEVANAGARI <= $scriptblock && $scriptblock <= UCDN::SCRIPT_MALAYALAM) {
				$this->shaper = "I";
			} // INDIC shaper
			else if ($scriptblock == UCDN::SCRIPT_ARABIC || $scriptblock == UCDN::SCRIPT_SYRIAC) {
				$this->shaper = "A";
			} // ARABIC shaper
			else if ($scriptblock == UCDN::SCRIPT_NKO || $scriptblock == UCDN::SCRIPT_MANDAIC) {
				$this->shaper = "A";
			} // ARABIC shaper
			else if ($scriptblock == UCDN::SCRIPT_KHMER) {
				$this->shaper = "K";
			} // KHMER shaper
			else if ($scriptblock == UCDN::SCRIPT_THAI) {
				$this->shaper = "T";
			} // THAI shaper
			else if ($scriptblock == UCDN::SCRIPT_LAO) {
				$this->shaper = "L";
			} // LAO shaper
			else if ($scriptblock == UCDN::SCRIPT_SINHALA) {
				$this->shaper = "S";
			} // SINHALA shaper
			else if ($scriptblock == UCDN::SCRIPT_MYANMAR) {
				$this->shaper = "M";
			} // MYANMAR shaper
			else if ($scriptblock == UCDN::SCRIPT_NEW_TAI_LUE) {
				$this->shaper = "E";
			} // SEA South East Asian shaper
			else if ($scriptblock == UCDN::SCRIPT_CHAM) {
				$this->shaper = "E";
			}  // SEA South East Asian shaper
			else if ($scriptblock == UCDN::SCRIPT_TAI_THAM) {
				$this->shaper = "E";
			} // SEA South East Asian shaper
			else
				$this->shaper = "";
			// Get scripttag based on actual text script
			$scripttag = UCDN::$uni_scriptblock[$scriptblock];

			$GSUBscriptTag = '';
			$GSUBlangsys = '';
			$GPOSscriptTag = '';
			$GPOSlangsys = '';
			$is_old_spec = false;

			$ScriptLang = $this->mpdf->CurrentFont['GSUBScriptLang'];
			if (count($ScriptLang)) {
				list($GSUBscriptTag, $is_old_spec) = $this->_getOTLscriptTag($ScriptLang, $scripttag, $scriptblock, $this->shaper, $useOTL, 'GSUB');
				if ($this->mpdf->fontLanguageOverride && strpos($ScriptLang[$GSUBscriptTag], $this->mpdf->fontLanguageOverride) !== false) {
					$GSUBlangsys = str_pad($this->mpdf->fontLanguageOverride, 4);
				} else if ($GSUBscriptTag && isset($ScriptLang[$GSUBscriptTag]) && $ScriptLang[$GSUBscriptTag] != '') {
					$GSUBlangsys = $this->_getOTLLangTag($this->mpdf->currentLang, $ScriptLang[$GSUBscriptTag]);
				}
			}
			$ScriptLang = $this->mpdf->CurrentFont['GPOSScriptLang'];

			// NB If after GSUB, the same script/lang exist for GPOS, just use these...
			if ($GSUBscriptTag && $GSUBlangsys && isset($ScriptLang[$GSUBscriptTag]) && strpos($ScriptLang[$GSUBscriptTag], $GSUBlangsys) !== false) {
				$GPOSlangsys = $GSUBlangsys;
				$GPOSscriptTag = $GSUBscriptTag;
			}

			// else repeat for GPOS
			// [Font XBRiyaz has GSUB tables for latn, but not GPOS for latn]
			else if (count($ScriptLang)) {
				list($GPOSscriptTag, $dummy) = $this->_getOTLscriptTag($ScriptLang, $scripttag, $scriptblock, $this->shaper, $useOTL, 'GPOS');
				if ($GPOSscriptTag && $this->mpdf->fontLanguageOverride && strpos($ScriptLang[$GPOSscriptTag], $this->mpdf->fontLanguageOverride) !== false) {
					$GPOSlangsys = str_pad($this->mpdf->fontLanguageOverride, 4);
				} else if ($GPOSscriptTag && isset($ScriptLang[$GPOSscriptTag]) && $ScriptLang[$GPOSscriptTag] != '') {
					$GPOSlangsys = $this->_getOTLLangTag($this->mpdf->currentLang, $ScriptLang[$GPOSscriptTag]);
				}
			}

			////////////////////////////////////////////////////////////////
			// This is just for the font_dump_OTL utility to set script and langsys override
			if (isset($this->mpdf->overrideOTLsettings) && isset($this->mpdf->overrideOTLsettings[$this->fontkey])) {
				$GSUBscriptTag = $GPOSscriptTag = $this->mpdf->overrideOTLsettings[$this->fontkey]['script'];
				$GSUBlangsys = $GPOSlangsys = $this->mpdf->overrideOTLsettings[$this->fontkey]['lang'];
			}
			////////////////////////////////////////////////////////////////

			if (!$GSUBscriptTag && !$GSUBlangsys && !$GPOSscriptTag && !$GPOSlangsys) {
				// Remove ZWJ and ZWNJ
				for ($i = 0; $i < count($this->OTLdata); $i++) {
					if ($this->OTLdata[$i]['uni'] == 8204 || $this->OTLdata[$i]['uni'] == 8205) {
						array_splice($this->OTLdata, $i, 1);
					}
				}
				$this->schOTLdata[$sch] = $this->OTLdata;
				$this->OTLdata = array();
				continue;
			}

			// Don't use MYANMAR shaper unless using v2 scripttag
			if ($this->shaper == 'M' && $GSUBscriptTag != 'mym2') {
				$this->shaper = '';
			}

			$GSUBFeatures = (isset($this->mpdf->CurrentFont['GSUBFeatures'][$GSUBscriptTag][$GSUBlangsys]) ? $this->mpdf->CurrentFont['GSUBFeatures'][$GSUBscriptTag][$GSUBlangsys] : false);
			$GPOSFeatures = (isset($this->mpdf->CurrentFont['GPOSFeatures'][$GPOSscriptTag][$GPOSlangsys]) ? $this->mpdf->CurrentFont['GPOSFeatures'][$GPOSscriptTag][$GPOSlangsys] : false);

			$this->assocLigs = array(); // Ligatures[$posarr lpos] => nc
			$this->assocMarks = array();  // assocMarks[$posarr mpos] => array(compID, ligPos)

			if (!isset($this->GDEFdata[$this->fontkey]['GSUBGPOStables'])) {
				$this->ttfOTLdata = $this->GDEFdata[$this->fontkey]['GSUBGPOStables'] = file_get_contents(_MPDF_TTFONTDATAPATH . $this->fontkey . '.GSUBGPOStables.dat', 'rb');
				if (!$this->ttfOTLdata) {
					throw new MpdfException('Can\'t open file ' . _MPDF_TTFONTDATAPATH . $this->fontkey . '.GSUBGPOStables.dat');
				}
			} else {
				$this->ttfOTLdata = $this->GDEFdata[$this->fontkey]['GSUBGPOStables'];
			}


			if ($this->debugOTL) {
				$this->_dumpproc('BEGIN', '-', '-', '-', '-', -1, '-', 0);
			}


////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////
/////////  LINE BREAKING FOR KHMER, THAI + LAO /////////////////
////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////
			// Insert U+200B at word boundaries using dictionaries
			if ($this->mpdf->useDictionaryLBR && ($this->shaper == "K" || $this->shaper == "T" || $this->shaper == "L")) {
				// Sets $this->OTLdata[$i]['wordend']=true at possible end of word boundaries
				$this->SEAlineBreaking();
			}
			// Insert U+200B at word boundaries for Tibetan
			else if ($this->mpdf->useTibetanLBR && $scriptblock == UCDN::SCRIPT_TIBETAN) {
				// Sets $this->OTLdata[$i]['wordend']=true at possible end of word boundaries
				$this->TibetanlineBreaking();
			}
////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////
//////////       GSUB          /////////////////////////////////
////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////
			if (($useOTL & 0xFF) && $GSUBscriptTag && $GSUBlangsys && $GSUBFeatures) {

				// 4. Load GSUB data, Coverage & Lookups
				//=================================================================

				$this->GSUBfont = $this->fontkey . '.GSUB.' . $GSUBscriptTag . '.' . $GSUBlangsys;

				if (!isset($this->GSUBdata[$this->GSUBfont])) {
					if (file_exists(_MPDF_TTFONTDATAPATH . $this->mpdf->CurrentFont['fontkey'] . '.GSUB.' . $GSUBscriptTag . '.' . $GSUBlangsys . '.php')) {
						include_once(_MPDF_TTFONTDATAPATH . $this->mpdf->CurrentFont['fontkey'] . '.GSUB.' . $GSUBscriptTag . '.' . $GSUBlangsys . '.php');
						$this->GSUBdata[$this->GSUBfont]['rtlSUB'] = $rtlSUB;
						$this->GSUBdata[$this->GSUBfont]['finals'] = $finals;
						if ($this->shaper == 'I') {
							$this->GSUBdata[$this->GSUBfont]['rphf'] = $rphf;
							$this->GSUBdata[$this->GSUBfont]['half'] = $half;
							$this->GSUBdata[$this->GSUBfont]['pref'] = $pref;
							$this->GSUBdata[$this->GSUBfont]['blwf'] = $blwf;
							$this->GSUBdata[$this->GSUBfont]['pstf'] = $pstf;
						}
					} else {
						$this->GSUBdata[$this->GSUBfont] = array('rtlSUB' => array(), 'rphf' => array(), 'rphf' => array(),
							'pref' => array(), 'blwf' => array(), 'pstf' => array(), 'finals' => ''
						);
					}
				}

				if (!isset($this->GSUBdata[$this->fontkey])) {
					include(_MPDF_TTFONTDATAPATH . $this->fontkey . '.GSUBdata.php');
					$this->GSLuCoverage = $this->GSUBdata[$this->fontkey]['GSLuCoverage'] = $GSLuCoverage;
				} else {
					$this->GSLuCoverage = $this->GSUBdata[$this->fontkey]['GSLuCoverage'];
				}

				$this->GSUBLookups = $this->mpdf->CurrentFont['GSUBLookups'];


				// 5(A). GSUB - Shaper - ARABIC
				//==============================
				if ($this->shaper == 'A') {
					//-----------------------------------------------------------------------------------
					// a. Apply initial GSUB Lookups (in order specified in lookup list but only selecting from certain tags)
					//-----------------------------------------------------------------------------------
					$tags = 'locl ccmp';
					$omittags = '';
					$usetags = $tags;
					if (!empty($this->mpdf->OTLtags)) {
						$usetags = $this->_applyTagSettings($tags, $GSUBFeatures, $omittags, true);
					}
					$this->_applyGSUBrules($usetags, $GSUBscriptTag, $GSUBlangsys);

					//-----------------------------------------------------------------------------------
					// b. Apply context-specific forms GSUB Lookups (initial, isolated, medial, final)
					//-----------------------------------------------------------------------------------
					// Arab and Syriac are the only scripts requiring the special joining - which takes the place of
					// isol fina medi init rules in GSUB (+ fin2 fin3 med2 in Syriac syrc)
					$tags = 'isol fina fin2 fin3 medi med2 init';
					$omittags = '';
					$usetags = $tags;
					if (!empty($this->mpdf->OTLtags)) {
						$usetags = $this->_applyTagSettings($tags, $GSUBFeatures, $omittags, true);
					}

					$this->arabGlyphs = $this->GSUBdata[$this->GSUBfont]['rtlSUB'];

					$gcms = explode("| ", $this->GlyphClassMarks);
					$gcm = array();
					foreach ($gcms AS $g) {
						$gcm[hexdec($g)] = 1;
					}
					$this->arabTransparentJoin = $this->arabTransparent + $gcm;
					$this->arabic_shaper($usetags, $GSUBscriptTag);

					//-----------------------------------------------------------------------------------
					// c. Set Kashida points (after joining occurred - medi, fina, init) but before other substitutions
					//-----------------------------------------------------------------------------------
					//if ($scriptblock == UCDN::SCRIPT_ARABIC ) {
					for ($i = 0; $i < count($this->OTLdata); $i++) {
						// Put the kashida marker on the character BEFORE which is inserted the kashida
						// Kashida marker is inverse of priority i.e. Priority 1 => 7, Priority 7 => 1.
						// Priority 1   User-inserted Kashida 0640 = Tatweel
						// The user entered a Kashida in a position
						// Position: Before the user-inserted kashida
						if ($this->OTLdata[$i]['uni'] == 0x0640) {
							$this->OTLdata[$i]['GPOSinfo']['kashida'] = 8; // Put before the next character
						}

						// Priority 2   Seen (0633)  FEB3, FEB4; Sad (0635)  FEBB, FEBC
						// Initial or medial form
						// Connecting to the next character
						// Position: After the character
						else if ($this->OTLdata[$i]['uni'] == 0xFEB3 || $this->OTLdata[$i]['uni'] == 0xFEB4 || $this->OTLdata[$i]['uni'] == 0xFEBB || $this->OTLdata[$i]['uni'] == 0xFEBC) {
							$checkpos = $i + 1;
							while (isset($this->OTLdata[$checkpos]) && strpos($this->GlyphClassMarks, $this->OTLdata[$checkpos]['hex']) !== false) {
								$checkpos++;
							}
							if (isset($this->OTLdata[$checkpos])) {
								$this->OTLdata[$checkpos]['GPOSinfo']['kashida'] = 7; // Put after marks on next character
							}
						}

						// Priority 3   Taa Marbutah (0629) FE94; Haa (062D) FEA2; Dal (062F) FEAA
						// Final form
						// Connecting to previous character
						// Position: Before the character
						else if ($this->OTLdata[$i]['uni'] == 0xFE94 || $this->OTLdata[$i]['uni'] == 0xFEA2 || $this->OTLdata[$i]['uni'] == 0xFEAA) {
							$this->OTLdata[$i]['GPOSinfo']['kashida'] = 6;
						}

						// Priority 4   Alef (0627) FE8E; Tah (0637) FEC2; Lam (0644) FEDE; Kaf (0643)  FEDA; Gaf (06AF) FB93
						// Final form
						// Connecting to previous character
						// Position: Before the character
						else if ($this->OTLdata[$i]['uni'] == 0xFE8E || $this->OTLdata[$i]['uni'] == 0xFEC2 || $this->OTLdata[$i]['uni'] == 0xFEDE || $this->OTLdata[$i]['uni'] == 0xFEDA || $this->OTLdata[$i]['uni'] == 0xFB93) {
							$this->OTLdata[$i]['GPOSinfo']['kashida'] = 5;
						}

						// Priority 5   RA (0631) FEAE; Ya (064A)  FEF2 FEF4; Alef Maqsurah (0649) FEF0 FBE9
						// Final or Medial form
						// Connected to preceding medial BAA (0628) = FE92
						// Position: Before preceding medial Baa
						// Although not mentioned in spec, added Farsi Yeh (06CC) FBFD FBFF; equivalent to 064A or 0649
						else if ($this->OTLdata[$i]['uni'] == 0xFEAE || $this->OTLdata[$i]['uni'] == 0xFEF2 || $this->OTLdata[$i]['uni'] == 0xFEF0 || $this->OTLdata[$i]['uni'] == 0xFEF4 || $this->OTLdata[$i]['uni'] == 0xFBE9 || $this->OTLdata[$i]['uni'] == 0xFBFD || $this->OTLdata[$i]['uni'] == 0xFBFF
						) {
							$checkpos = $i - 1;
							while (isset($this->OTLdata[$checkpos]) && strpos($this->GlyphClassMarks, $this->OTLdata[$checkpos]['hex']) !== false) {
								$checkpos--;
							}
							if (isset($this->OTLdata[$checkpos]) && $this->OTLdata[$checkpos]['uni'] == 0xFE92) {
								$this->OTLdata[$checkpos]['GPOSinfo']['kashida'] = 4; // ******* Before preceding BAA
							}
						}

						// Priority 6   WAW (0648) FEEE; Ain (0639) FECA; Qaf (0642) FED6; Fa (0641) FED2
						// Final form
						// Connecting to previous character
						// Position: Before the character
						else if ($this->OTLdata[$i]['uni'] == 0xFEEE || $this->OTLdata[$i]['uni'] == 0xFECA || $this->OTLdata[$i]['uni'] == 0xFED6 || $this->OTLdata[$i]['uni'] == 0xFED2) {
							$this->OTLdata[$i]['GPOSinfo']['kashida'] = 3;
						}

						// Priority 7   Other connecting characters
						// Final form
						// Connecting to previous character
						// Position: Before the character
						/* This isn't in the spec, but using MS WORD as a basis, give a lower priority to the 3 characters already checked
						  in (5) above. Test case:
						  &#x62e;&#x652;&#x631;&#x64e;&#x649;&#x670;
						  &#x641;&#x64e;&#x62a;&#x64f;&#x630;&#x64e;&#x643;&#x651;&#x650;&#x631;
						 */

						if (!isset($this->OTLdata[$i]['GPOSinfo']['kashida'])) {
							if (strpos($this->GSUBdata[$this->GSUBfont]['finals'], $this->OTLdata[$i]['hex']) !== false) { // ANY OTHER FINAL FORM
								$this->OTLdata[$i]['GPOSinfo']['kashida'] = 2;
							} else if (strpos('0FEAE 0FEF0 0FEF2', $this->OTLdata[$i]['hex']) !== false) { // not already included in 5 above
								$this->OTLdata[$i]['GPOSinfo']['kashida'] = 1;
							}
						}
					}

					//-----------------------------------------------------------------------------------
					// d. Apply Presentation Forms GSUB Lookups (+ any discretionary) - Apply one at a time in Feature order
					//-----------------------------------------------------------------------------------
					$tags = 'rlig calt liga clig mset';

					$omittags = 'locl ccmp nukt akhn rphf rkrf pref blwf abvf half pstf cfar vatu cjct init medi fina isol med2 fin2 fin3 ljmo vjmo tjmo';
					$usetags = $tags;
					if (!empty($this->mpdf->OTLtags)) {
						$usetags = $this->_applyTagSettings($tags, $GSUBFeatures, $omittags, false);
					}

					$ts = explode(' ', $usetags);
					foreach ($ts AS $ut) { //  - Apply one at a time in Feature order
						$this->_applyGSUBrules($ut, $GSUBscriptTag, $GSUBlangsys);
					}
					//-----------------------------------------------------------------------------------
					// e. NOT IN SPEC
					// If space precedes a mark -> substitute a &nbsp; before the Mark, to prevent line breaking Test:
					//-----------------------------------------------------------------------------------
					for ($ptr = 1; $ptr < count($this->OTLdata); $ptr++) {
						if ($this->OTLdata[$ptr]['general_category'] == UCDN::UNICODE_GENERAL_CATEGORY_NON_SPACING_MARK && $this->OTLdata[$ptr - 1]['uni'] == 32) {
							$this->OTLdata[$ptr - 1]['uni'] = 0xa0;
							$this->OTLdata[$ptr - 1]['hex'] = '000A0';
						}
					}
				}

				// 5(I). GSUB - Shaper - INDIC and SINHALA and KHMER
				//===================================
				else if ($this->shaper == 'I' || $this->shaper == 'K' || $this->shaper == 'S') {
					$this->restrictToSyllable = true;
					//-----------------------------------------------------------------------------------
					// a. First decompose/compose split mattras
					// (normalize) ??????? Nukta/Halant order etc ??????????????????????????????????????????????????????????????????????????
					//-----------------------------------------------------------------------------------
					for ($ptr = 0; $ptr < count($this->OTLdata); $ptr++) {
						$char = $this->OTLdata[$ptr]['uni'];
						$sub = INDIC::decompose_indic($char);
						if ($sub) {
							$newinfo = array();
							for ($i = 0; $i < count($sub); $i++) {
								$newinfo[$i] = array();
								$ucd_record = UCDN::get_ucd_record($sub[$i]);
								$newinfo[$i]['general_category'] = $ucd_record[0];
								$newinfo[$i]['bidi_type'] = $ucd_record[2];
								$charasstr = $this->unicode_hex($sub[$i]);
								if (strpos($this->GlyphClassMarks, $charasstr) !== false) {
									$newinfo[$i]['group'] = 'M';
								} else {
									$newinfo[$i]['group'] = 'C';
								}
								$newinfo[$i]['uni'] = $sub[$i];
								$newinfo[$i]['hex'] = $charasstr;
							}
							array_splice($this->OTLdata, $ptr, 1, $newinfo);
							$ptr += count($sub) - 1;
						}
						/* Only Composition-exclusion exceptions that we want to recompose. */
						if ($this->shaper == 'I') {
							if ($char == 0x09AF && isset($this->OTLdata[$ptr + 1]) && $this->OTLdata[$ptr + 1]['uni'] == 0x09BC) {
								$sub = 0x09DF;
								$newinfo = array();
								$newinfo[0] = array();
								$ucd_record = UCDN::get_ucd_record($sub);
								$newinfo[0]['general_category'] = $ucd_record[0];
								$newinfo[0]['bidi_type'] = $ucd_record[2];
								$newinfo[0]['group'] = 'C';
								$newinfo[0]['uni'] = $sub;
								$newinfo[0]['hex'] = $this->unicode_hex($sub);
								array_splice($this->OTLdata, $ptr, 2, $newinfo);
							}
						}
					}
					//-----------------------------------------------------------------------------------
					// b. Analyse characters - group as syllables/clusters (Indic); invalid diacritics; add dotted circle
					//-----------------------------------------------------------------------------------
					$indic_category_string = '';
					foreach ($this->OTLdata AS $eid => $c) {
						INDIC::set_indic_properties($this->OTLdata[$eid], $scriptblock); // sets ['indic_category'] and ['indic_position']
						//$c['general_category']
						//$c['combining_class']
						//$c['uni'] =  $char;

						$indic_category_string .= INDIC::$indic_category_char[$this->OTLdata[$eid]['indic_category']];
					}

					$broken_syllables = false;
					if ($this->shaper == 'I') {
						INDIC::set_syllables($this->OTLdata, $indic_category_string, $broken_syllables);
					} else if ($this->shaper == 'S') {
						INDIC::set_syllables_sinhala($this->OTLdata, $indic_category_string, $broken_syllables);
					} else if ($this->shaper == 'K') {
						INDIC::set_syllables_khmer($this->OTLdata, $indic_category_string, $broken_syllables);
					}
					$indic_category_string = '';

					//-----------------------------------------------------------------------------------
					// c. Initial Re-ordering (Indic / Khmer / Sinhala)
					//-----------------------------------------------------------------------------------
					// Find base consonant
					// Decompose/compose and reorder Matras
					// Reorder marks to canonical order

					$indic_config = INDIC::$indic_configs[$scriptblock];
					$dottedcircle = false;
					if ($broken_syllables) {
						if ($this->mpdf->_charDefined($this->mpdf->fonts[$this->fontkey]['cw'], 0x25CC)) {
							$dottedcircle = array();
							$ucd_record = UCDN::get_ucd_record(0x25CC);
							$dottedcircle[0]['general_category'] = $ucd_record[0];
							$dottedcircle[0]['bidi_type'] = $ucd_record[2];
							$dottedcircle[0]['group'] = 'C';
							$dottedcircle[0]['uni'] = 0x25CC;
							$dottedcircle[0]['indic_category'] = INDIC::OT_DOTTEDCIRCLE;
							$dottedcircle[0]['indic_position'] = INDIC::POS_BASE_C;

							$dottedcircle[0]['hex'] = '025CC';  // TEMPORARY *****
						}
					}
					INDIC::initial_reordering($this->OTLdata, $this->GSUBdata[$this->GSUBfont], $broken_syllables, $indic_config, $scriptblock, $is_old_spec, $dottedcircle);

					//-----------------------------------------------------------------------------------
					// d. Apply initial and basic shaping forms GSUB Lookups (one at a time)
					//-----------------------------------------------------------------------------------
					if ($this->shaper == 'I' || $this->shaper == 'S') {
						$tags = 'locl ccmp nukt akhn rphf rkrf pref blwf half pstf vatu cjct';
					} else if ($this->shaper == 'K') {
						$tags = 'locl ccmp pref blwf abvf pstf cfar';
					}
					$this->_applyGSUBrulesIndic($tags, $GSUBscriptTag, $GSUBlangsys, $is_old_spec);

					//-----------------------------------------------------------------------------------
					// e. Final Re-ordering (Indic / Khmer / Sinhala)
					//-----------------------------------------------------------------------------------
					// Reorder matras
					// Reorder reph
					// Reorder pre-base reordering consonants:

					INDIC::final_reordering($this->OTLdata, $this->GSUBdata[$this->GSUBfont], $indic_config, $scriptblock, $is_old_spec);

					//-----------------------------------------------------------------------------------
					// f. Apply 'init' feature to first syllable in word (indicated by ['mask']) INDIC::FLAG(INDIC::INIT);
					//-----------------------------------------------------------------------------------
					if ($this->shaper == 'I' || $this->shaper == 'S') {
						$tags = 'init';
						$this->_applyGSUBrulesIndic($tags, $GSUBscriptTag, $GSUBlangsys, $is_old_spec);
					}

					//-----------------------------------------------------------------------------------
					// g. Apply Presentation Forms GSUB Lookups (+ any discretionary)
					//-----------------------------------------------------------------------------------
					$tags = 'pres abvs blws psts haln rlig calt liga clig mset';

					$omittags = 'locl ccmp nukt akhn rphf rkrf pref blwf abvf half pstf cfar vatu cjct init medi fina isol med2 fin2 fin3 ljmo vjmo tjmo';
					$usetags = $tags;
					if (!empty($this->mpdf->OTLtags)) {
						$usetags = $this->_applyTagSettings($tags, $GSUBFeatures, $omittags, false);
					}
					if ($this->shaper == 'K') {  // Features are applied one at a time, working through each codepoint
						$this->_applyGSUBrulesSingly($usetags, $GSUBscriptTag, $GSUBlangsys);
					} else {
						$this->_applyGSUBrules($usetags, $GSUBscriptTag, $GSUBlangsys);
					}
					$this->restrictToSyllable = false;
				}


				// 5(M). GSUB - Shaper - MYANMAR (ONLY mym2)
				//==============================
				// NB Old style 'mymr' is left to go through the default shaper
				else if ($this->shaper == 'M') {
					$this->restrictToSyllable = true;
					//-----------------------------------------------------------------------------------
					// a. Analyse characters - group as syllables/clusters (Myanmar); invalid diacritics; add dotted circle
					//-----------------------------------------------------------------------------------
					$myanmar_category_string = '';
					foreach ($this->OTLdata AS $eid => $c) {
						MYANMAR::set_myanmar_properties($this->OTLdata[$eid]); // sets ['myanmar_category'] and ['myanmar_position']
						$myanmar_category_string .= MYANMAR::$myanmar_category_char[$this->OTLdata[$eid]['myanmar_category']];
					}
					$broken_syllables = false;
					MYANMAR::set_syllables($this->OTLdata, $myanmar_category_string, $broken_syllables);
					$myanmar_category_string = '';

					//-----------------------------------------------------------------------------------
					// b. Re-ordering (Myanmar mym2)
					//-----------------------------------------------------------------------------------
					$dottedcircle = false;
					if ($broken_syllables) {
						if ($this->mpdf->_charDefined($this->mpdf->fonts[$this->fontkey]['cw'], 0x25CC)) {
							$dottedcircle = array();
							$ucd_record = UCDN::get_ucd_record(0x25CC);
							$dottedcircle[0]['general_category'] = $ucd_record[0];
							$dottedcircle[0]['bidi_type'] = $ucd_record[2];
							$dottedcircle[0]['group'] = 'C';
							$dottedcircle[0]['uni'] = 0x25CC;
							$dottedcircle[0]['myanmar_category'] = MYANMAR::OT_DOTTEDCIRCLE;
							$dottedcircle[0]['myanmar_position'] = MYANMAR::POS_BASE_C;
							$dottedcircle[0]['hex'] = '025CC';
						}
					}
					MYANMAR::reordering($this->OTLdata, $this->GSUBdata[$this->GSUBfont], $broken_syllables, $dottedcircle);

					//-----------------------------------------------------------------------------------
					// c. Apply initial and basic shaping forms GSUB Lookups (one at a time)
					//-----------------------------------------------------------------------------------

					$tags = 'locl ccmp rphf pref blwf pstf';
					$this->_applyGSUBrulesMyanmar($tags, $GSUBscriptTag, $GSUBlangsys);

					//-----------------------------------------------------------------------------------
					// d. Apply Presentation Forms GSUB Lookups (+ any discretionary)
					//-----------------------------------------------------------------------------------
					$tags = 'pres abvs blws psts haln rlig calt liga clig mset';
					$omittags = 'locl ccmp nukt akhn rphf rkrf pref blwf abvf half pstf cfar vatu cjct init medi fina isol med2 fin2 fin3 ljmo vjmo tjmo';
					$usetags = $tags;
					if (!empty($this->mpdf->OTLtags)) {
						$usetags = $this->_applyTagSettings($tags, $GSUBFeatures, $omittags, false);
					}
					$this->_applyGSUBrules($usetags, $GSUBscriptTag, $GSUBlangsys);
					$this->restrictToSyllable = false;
				}


				// 5(E). GSUB - Shaper - SEA South East Asian (New Tai Lue, Cham, Tai Tam)
				//==============================
				else if ($this->shaper == 'E') {
					/* HarfBuzz says: If the designer designed the font for the 'DFLT' script,
					 * use the default shaper.  Otherwise, use the SEA shaper.
					 * Note that for some simple scripts, there may not be *any*
					 * GSUB/GPOS needed, so there may be no scripts found! */

					$this->restrictToSyllable = true;
					//-----------------------------------------------------------------------------------
					// a. Analyse characters - group as syllables/clusters (Indic); invalid diacritics; add dotted circle
					//-----------------------------------------------------------------------------------
					$sea_category_string = '';
					foreach ($this->OTLdata AS $eid => $c) {
						SEA::set_sea_properties($this->OTLdata[$eid], $scriptblock); // sets ['sea_category'] and ['sea_position']
						//$c['general_category']
						//$c['combining_class']
						//$c['uni'] =  $char;

						$sea_category_string .= SEA::$sea_category_char[$this->OTLdata[$eid]['sea_category']];
					}

					$broken_syllables = false;
					SEA::set_syllables($this->OTLdata, $sea_category_string, $broken_syllables);
					$sea_category_string = '';

					//-----------------------------------------------------------------------------------
					// b. Apply locl and ccmp shaping forms - before initial re-ordering; GSUB Lookups (one at a time)
					//-----------------------------------------------------------------------------------
					$tags = 'locl ccmp';
					$this->_applyGSUBrulesSingly($tags, $GSUBscriptTag, $GSUBlangsys);

					//-----------------------------------------------------------------------------------
					// c. Initial Re-ordering
					//-----------------------------------------------------------------------------------
					// Find base consonant
					// Decompose/compose and reorder Matras
					// Reorder marks to canonical order

					$dottedcircle = false;
					if ($broken_syllables) {
						if ($this->mpdf->_charDefined($this->mpdf->fonts[$this->fontkey]['cw'], 0x25CC)) {
							$dottedcircle = array();
							$ucd_record = UCDN::get_ucd_record(0x25CC);
							$dottedcircle[0]['general_category'] = $ucd_record[0];
							$dottedcircle[0]['bidi_type'] = $ucd_record[2];
							$dottedcircle[0]['group'] = 'C';
							$dottedcircle[0]['uni'] = 0x25CC;
							$dottedcircle[0]['sea_category'] = SEA::OT_GB;
							$dottedcircle[0]['sea_position'] = SEA::POS_BASE_C;

							$dottedcircle[0]['hex'] = '025CC';  // TEMPORARY *****
						}
					}
					SEA::initial_reordering($this->OTLdata, $this->GSUBdata[$this->GSUBfont], $broken_syllables, $scriptblock, $dottedcircle);

					//-----------------------------------------------------------------------------------
					// d. Apply basic shaping forms GSUB Lookups (one at a time)
					//-----------------------------------------------------------------------------------
					$tags = 'pref abvf blwf pstf';
					$this->_applyGSUBrulesSingly($tags, $GSUBscriptTag, $GSUBlangsys);

					//-----------------------------------------------------------------------------------
					// e. Final Re-ordering
					//-----------------------------------------------------------------------------------

					SEA::final_reordering($this->OTLdata, $this->GSUBdata[$this->GSUBfont], $scriptblock);

					//-----------------------------------------------------------------------------------
					// f. Apply Presentation Forms GSUB Lookups (+ any discretionary)
					//-----------------------------------------------------------------------------------
					$tags = 'pres abvs blws psts';

					$omittags = 'locl ccmp nukt akhn rphf rkrf pref blwf abvf half pstf cfar vatu cjct init medi fina isol med2 fin2 fin3 ljmo vjmo tjmo';
					$usetags = $tags;
					if (!empty($this->mpdf->OTLtags)) {
						$usetags = $this->_applyTagSettings($tags, $GSUBFeatures, $omittags, false);
					}
					$this->_applyGSUBrules($usetags, $GSUBscriptTag, $GSUBlangsys);
					$this->restrictToSyllable = false;
				}


				// 5(D). GSUB - Shaper - DEFAULT (including THAI and LAO and MYANMAR v1 [mymr] and TIBETAN)
				//==============================
				else { // DEFAULT
					//-----------------------------------------------------------------------------------
					// a. First decompose/compose in Thai / Lao - Tibetan
					//-----------------------------------------------------------------------------------
					// Decomposition for THAI or LAO
					/* This function implements the shaping logic documented here:
					 *
					 *   http://linux.thai.net/~thep/th-otf/shaping.html
					 *
					 * The first shaping rule listed there is needed even if the font has Thai
					 * OpenType tables.
					 *
					 *
					 * The following is NOT specified in the MS OT Thai spec, however, it seems
					 * to be what Uniscribe and other engines implement.  According to Eric Muller:
					 *
					 * When you have a SARA AM, decompose it in NIKHAHIT + SARA AA, *and* move the
					 * NIKHAHIT backwards over any tone mark (0E48-0E4B).
					 *
					 * <0E14, 0E4B, 0E33> -> <0E14, 0E4D, 0E4B, 0E32>
					 *
					 * This reordering is legit only when the NIKHAHIT comes from a SARA AM, not
					 * when it's there to start with. The string <0E14, 0E4B, 0E4D> is probably
					 * not what a user wanted, but the rendering is nevertheless nikhahit above
					 * chattawa.
					 *
					 * Same for Lao.
					 *
					 *          Thai        Lao
					 * SARA AM:     U+0E33  U+0EB3
					 * SARA AA:     U+0E32  U+0EB2
					 * Nikhahit:    U+0E4D  U+0ECD
					 *
					 * Testing shows that Uniscribe reorder the following marks:
					 * Thai:    <0E31,0E34..0E37,0E47..0E4E>
					 * Lao: <0EB1,0EB4..0EB7,0EC7..0ECE>
					 *
					 * Lao versions are the same as Thai + 0x80.
					 */
					if ($this->shaper == 'T' || $this->shaper == 'L') {
						for ($ptr = 0; $ptr < count($this->OTLdata); $ptr++) {
							$char = $this->OTLdata[$ptr]['uni'];
							if (($char & ~0x0080) == 0x0E33) { // if SARA_AM (U+0E33 or U+0EB3)
								$NIKHAHIT = $char + 0x1A;
								$SARA_AA = $char - 1;
								$sub = array($SARA_AA, $NIKHAHIT);

								$newinfo = array();
								$ucd_record = UCDN::get_ucd_record($sub[0]);
								$newinfo[0]['general_category'] = $ucd_record[0];
								$newinfo[0]['bidi_type'] = $ucd_record[2];
								$charasstr = $this->unicode_hex($sub[0]);
								if (strpos($this->GlyphClassMarks, $charasstr) !== false) {
									$newinfo[0]['group'] = 'M';
								} else {
									$newinfo[0]['group'] = 'C';
								}
								$newinfo[0]['uni'] = $sub[0];
								$newinfo[0]['hex'] = $charasstr;
								$this->OTLdata[$ptr] = $newinfo[0]; // Substitute SARA_AM => SARA_AA

								$ntones = 0; // number of (preceding) tone marks
								// IS_TONE_MARK ((x) & ~0x0080, 0x0E34 - 0x0E37, 0x0E47 - 0x0E4E, 0x0E31)
								while (isset($this->OTLdata[$ptr - 1 - $ntones]) && (
								($this->OTLdata[$ptr - 1 - $ntones]['uni'] & ~0x0080) == 0x0E31 ||
								(($this->OTLdata[$ptr - 1 - $ntones]['uni'] & ~0x0080) >= 0x0E34 &&
								($this->OTLdata[$ptr - 1 - $ntones]['uni'] & ~0x0080) <= 0x0E37) ||
								(($this->OTLdata[$ptr - 1 - $ntones]['uni'] & ~0x0080) >= 0x0E47 &&
								($this->OTLdata[$ptr - 1 - $ntones]['uni'] & ~0x0080) <= 0x0E4E)
								)
								) {
									$ntones++;
								}

								$newinfo = array();
								$ucd_record = UCDN::get_ucd_record($sub[1]);
								$newinfo[0]['general_category'] = $ucd_record[0];
								$newinfo[0]['bidi_type'] = $ucd_record[2];
								$charasstr = $this->unicode_hex($sub[1]);
								if (strpos($this->GlyphClassMarks, $charasstr) !== false) {
									$newinfo[0]['group'] = 'M';
								} else {
									$newinfo[0]['group'] = 'C';
								}
								$newinfo[0]['uni'] = $sub[1];
								$newinfo[0]['hex'] = $charasstr;
								// Insert NIKAHIT
								array_splice($this->OTLdata, $ptr - $ntones, 0, $newinfo);

								$ptr++;
							}
						}
					}

					if ($scriptblock == UCDN::SCRIPT_TIBETAN) {
						// =========================
						// Reordering TIBETAN
						// =========================
						// Tibetan does not need to need a shaper generally, as long as characters are presented in the correct order
						// so we will do one minor change here:
						// From ICU: If the present character is a number, and the next character is a pre-number combining mark
						// then the two characters are reordered
						// From MS OTL spec the following are Digit modifiers (Md): 0F18–0F19, 0F3E–0F3F
						// Digits: 0F20–0F33
						// On testing only 0x0F3F (pre-based mark) seems to need re-ordering
						for ($ptr = 0; $ptr < count($this->OTLdata) - 1; $ptr++) {
							if (INDIC::in_range($this->OTLdata[$ptr]['uni'], 0x0F20, 0x0F33) && $this->OTLdata[$ptr + 1]['uni'] == 0x0F3F) {
								$tmp = $this->OTLdata[$ptr + 1];
								$this->OTLdata[$ptr + 1] = $this->OTLdata[$ptr];
								$this->OTLdata[$ptr] = $tmp;
							}
						}


						// =========================
						// Decomposition for TIBETAN
						// =========================
						/* Recommended, but does not seem to change anything...
						  for($ptr=0; $ptr<count($this->OTLdata); $ptr++) {
						  $char = $this->OTLdata[$ptr]['uni'];
						  $sub = INDIC::decompose_indic($char);
						  if ($sub) {
						  $newinfo = array();
						  for($i=0;$i<count($sub);$i++) {
						  $newinfo[$i] = array();
						  $ucd_record = UCDN::get_ucd_record($sub[$i]);
						  $newinfo[$i]['general_category'] = $ucd_record[0];
						  $newinfo[$i]['bidi_type'] = $ucd_record[2];
						  $charasstr = $this->unicode_hex($sub[$i]);
						  if (strpos($this->GlyphClassMarks, $charasstr)!==false) { $newinfo[$i]['group'] =  'M'; }
						  else { $newinfo[$i]['group'] =  'C'; }
						  $newinfo[$i]['uni'] =  $sub[$i];
						  $newinfo[$i]['hex'] =  $charasstr;
						  }
						  array_splice($this->OTLdata, $ptr, 1, $newinfo);
						  $ptr += count($sub)-1;
						  }
						  }
						 */
					}


					//-----------------------------------------------------------------------------------
					// b. Apply all GSUB Lookups (in order specified in lookup list)
					//-----------------------------------------------------------------------------------
					$tags = 'locl ccmp pref blwf abvf pstf pres abvs blws psts haln rlig calt liga clig mset  RQD';
					// pref blwf abvf pstf required for Tibetan
					// " RQD" is a non-standard tag in Garuda font - presumably intended to be used by default ? "ReQuireD"
					// Being a 3 letter tag is non-standard, and does not allow it to be set by font-feature-settings


					/* ?Add these until shapers witten?
					  Hangul:   ljmo vjmo tjmo
					 */

					$omittags = '';
					$useGSUBtags = $tags;
					if (!empty($this->mpdf->OTLtags)) {
						$useGSUBtags = $this->_applyTagSettings($tags, $GSUBFeatures, $omittags, false);
					}
					// APPLY GSUB rules (as long as not Latin + SmallCaps - but not OTL smcp)
					if (!(($this->mpdf->textvar & FC_SMALLCAPS) && $scriptblock == UCDN::SCRIPT_LATIN && strpos($useGSUBtags, 'smcp') === false)) {
						$this->_applyGSUBrules($useGSUBtags, $GSUBscriptTag, $GSUBlangsys);
					}
				}
			}

			// Shapers - KHMER & THAI & LAO - Replace Word boundary marker with U+200B
			// Also TIBETAN (no shaper)
			//=======================================================
			if (($this->shaper == "K" || $this->shaper == "T" || $this->shaper == "L") || $scriptblock == UCDN::SCRIPT_TIBETAN) {
				// Set up properties to insert a U+200B character
				$newinfo = array();
				//$newinfo[0] = array('general_category' => 1, 'bidi_type' => 14, 'group' => 'S', 'uni' => 0x200B, 'hex' => '0200B');
				$newinfo[0] = array(
					'general_category' => UCDN::UNICODE_GENERAL_CATEGORY_FORMAT,
					'bidi_type' => UCDN::BIDI_CLASS_BN,
					'group' => 'S', 'uni' => 0x200B, 'hex' => '0200B');
				// Then insert U+200B at (after) all word end boundaries
				for ($i = count($this->OTLdata) - 1; $i > 0; $i--) {
					// Make sure after GSUB that wordend has not been moved - check next char is not in the same syllable
					if (isset($this->OTLdata[$i]['wordend']) && $this->OTLdata[$i]['wordend'] &&
						isset($this->OTLdata[$i + 1]['uni']) && (!isset($this->OTLdata[$i + 1]['syllable']) || !isset($this->OTLdata[$i + 1]['syllable']) || $this->OTLdata[$i + 1]['syllable'] != $this->OTLdata[$i]['syllable'])) {
						array_splice($this->OTLdata, $i + 1, 0, $newinfo);
						$this->_updateLigatureMarks($i, 1);
					} else if ($this->OTLdata[$i]['uni'] == 0x2e) { // Word end if Full-stop.
						array_splice($this->OTLdata, $i + 1, 0, $newinfo);
						$this->_updateLigatureMarks($i, 1);
					}
				}
			}


			// Shapers - INDIC & ARABIC & KHMER & SINHALA  & MYANMAR - Remove ZWJ and ZWNJ
			//=======================================================
			if ($this->shaper == 'I' || $this->shaper == 'S' || $this->shaper == 'A' || $this->shaper == 'K' || $this->shaper == 'M') {
				// Remove ZWJ and ZWNJ
				for ($i = 0; $i < count($this->OTLdata); $i++) {
					if ($this->OTLdata[$i]['uni'] == 8204 || $this->OTLdata[$i]['uni'] == 8205) {
						array_splice($this->OTLdata, $i, 1);
						$this->_updateLigatureMarks($i, -1);
					}
				}
			}

//print_r($this->OTLdata); echo '<br />';
//print_r($this->assocMarks);  echo '<br />';
//print_r($this->assocLigs); exit;
////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////
//////////       GPOS          /////////////////////////////////
////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

			if (($useOTL & 0xFF) && $GPOSscriptTag && $GPOSlangsys && $GPOSFeatures) {
				$this->Entry = array();
				$this->Exit = array();

				// 6. Load GPOS data, Coverage & Lookups
				//=================================================================
				if (!isset($this->GPOSdata[$this->fontkey])) {
					include(_MPDF_TTFONTDATAPATH . $this->mpdf->CurrentFont['fontkey'] . '.GPOSdata.php');
					$this->LuCoverage = $this->GPOSdata[$this->fontkey]['LuCoverage'] = $LuCoverage;
				} else {
					$this->LuCoverage = $this->GPOSdata[$this->fontkey]['LuCoverage'];
				}

				$this->GPOSLookups = $this->mpdf->CurrentFont['GPOSLookups'];


				// 7. Select Feature tags to use (incl optional)
				//==============================
				$tags = 'abvm blwm mark mkmk curs cpsp dist requ'; // Default set
				/* 'requ' is not listed in the Microsoft registry of Feature tags
				  Found in Arial Unicode MS, it repositions the baseline for punctuation in Kannada script */

				// ZZZ96
				// Set kern to be included by default in non-Latin script (? just when shapers used)
				// Kern is used in some fonts to reposition marks etc. and is essential for correct display
				//if ($this->shaper) {$tags .= ' kern'; }
				if ($scriptblock != UCDN::SCRIPT_LATIN) {
					$tags .= ' kern';
				}

				$omittags = '';
				$usetags = $tags;
				if (!empty($this->mpdf->OTLtags)) {
					$usetags = $this->_applyTagSettings($tags, $GPOSFeatures, $omittags, false);
				}



				// 8. Get GPOS LookupList from Feature tags
				//==============================
				$LookupList = array();
				foreach ($GPOSFeatures AS $tag => $arr) {
					if (strpos($usetags, $tag) !== false) {
						foreach ($arr AS $lu) {
							$LookupList[$lu] = $tag;
						}
					}
				}
				ksort($LookupList);


				// 9. Apply GPOS Lookups (in order specified in lookup list but selecting from specified tags)
				//==============================
				// APPLY THE GPOS RULES (as long as not Latin + SmallCaps - but not OTL smcp)
				if (!(($this->mpdf->textvar & FC_SMALLCAPS) && $scriptblock == UCDN::SCRIPT_LATIN && strpos($useGSUBtags, 'smcp') === false)) {
					$this->_applyGPOSrules($LookupList, $is_old_spec);
					// (sets: $this->OTLdata[n]['GPOSinfo'] XPlacement YPlacement XAdvance Entry Exit )
				}

				// 10. Process cursive text
				//==============================
				if (count($this->Entry) || count($this->Exit)) {
					// RTL
					$incurs = false;
					for ($i = (count($this->OTLdata) - 1); $i >= 0; $i--) {
						if (isset($this->Entry[$i]) && isset($this->Entry[$i]['Y']) && $this->Entry[$i]['dir'] == 'RTL') {
							$nextbase = $i - 1; // Set as next base ignoring marks (next base reading RTL in logical oder
							while (isset($this->OTLdata[$nextbase]['hex']) && strpos($this->GlyphClassMarks, $this->OTLdata[$nextbase]['hex']) !== false) {
								$nextbase--;
							}
							if (isset($this->Exit[$nextbase]) && isset($this->Exit[$nextbase]['Y'])) {
								$diff = $this->Entry[$i]['Y'] - $this->Exit[$nextbase]['Y'];
								if ($incurs === false) {
									$incurs = $diff;
								} else {
									$incurs += $diff;
								}
								for ($j = ($i - 1); $j >= $nextbase; $j--) {
									if (isset($this->OTLdata[$j]['GPOSinfo']['YPlacement'])) {
										$this->OTLdata[$j]['GPOSinfo']['YPlacement'] += $incurs;
									} else {
										$this->OTLdata[$j]['GPOSinfo']['YPlacement'] = $incurs;
									}
								}
								if (isset($this->Exit[$i]['X']) && isset($this->Entry[$nextbase]['X'])) {
									$adj = -($this->Entry[$i]['X'] - $this->Exit[$nextbase]['X']);
									// If XAdvance is aplied - in order for PDF to position the Advance correctly need to place it on:
									// in RTL - the current glyph or the last of any associated marks
									if (isset($this->OTLdata[$nextbase + 1]['GPOSinfo']['XAdvance'])) {
										$this->OTLdata[$nextbase + 1]['GPOSinfo']['XAdvance'] += $adj;
									} else {
										$this->OTLdata[$nextbase + 1]['GPOSinfo']['XAdvance'] = $adj;
									}
								}
							} else {
								$incurs = false;
							}
						} else if (strpos($this->GlyphClassMarks, $this->OTLdata[$i]['hex']) !== false) {
							continue;
						} // ignore Marks
						else {
							$incurs = false;
						}
					}
					// LTR
					$incurs = false;
					for ($i = 0; $i < count($this->OTLdata); $i++) {
						if (isset($this->Exit[$i]) && isset($this->Exit[$i]['Y']) && $this->Exit[$i]['dir'] == 'LTR') {
							$nextbase = $i + 1; // Set as next base ignoring marks
							while (strpos($this->GlyphClassMarks, $this->OTLdata[$nextbase]['hex']) !== false) {
								$nextbase++;
							}
							if (isset($this->Entry[$nextbase]) && isset($this->Entry[$nextbase]['Y'])) {

								$diff = $this->Exit[$i]['Y'] - $this->Entry[$nextbase]['Y'];
								if ($incurs === false) {
									$incurs = $diff;
								} else {
									$incurs += $diff;
								}
								for ($j = ($i + 1); $j <= $nextbase; $j++) {
									if (isset($this->OTLdata[$j]['GPOSinfo']['YPlacement'])) {
										$this->OTLdata[$j]['GPOSinfo']['YPlacement'] += $incurs;
									} else {
										$this->OTLdata[$j]['GPOSinfo']['YPlacement'] = $incurs;
									}
								}
								if (isset($this->Exit[$i]['X']) && isset($this->Entry[$nextbase]['X'])) {
									$adj = -($this->Exit[$i]['X'] - $this->Entry[$nextbase]['X']);
									// If XAdvance is aplied - in order for PDF to position the Advance correctly need to place it on:
									// in LTR - the next glyph, ignoring marks
									if (isset($this->OTLdata[$nextbase]['GPOSinfo']['XAdvance'])) {
										$this->OTLdata[$nextbase]['GPOSinfo']['XAdvance'] += $adj;
									} else {
										$this->OTLdata[$nextbase]['GPOSinfo']['XAdvance'] = $adj;
									}
								}
							} else {
								$incurs = false;
							}
						} else if (strpos($this->GlyphClassMarks, $this->OTLdata[$i]['hex']) !== false) {
							continue;
						} // ignore Marks
						else {
							$incurs = false;
						}
					}
				}
			} // end GPOS

			if ($this->debugOTL) {
				$this->_dumpproc('END', '-', '-', '-', '-', 0, '-', 0);
				exit;
			}

			$this->schOTLdata[$sch] = $this->OTLdata;
			$this->OTLdata = array();
		} // END foreach subchunk
		// 11. Re-assemble and return text string
		//==============================
		$newGPOSinfo = array();
		$newOTLdata = array();
		$newchar_data = array();
		$newgroup = '';
		$e = '';
		$ectr = 0;

		for ($sch = 0; $sch <= $subchunk; $sch++) {
			for ($i = 0; $i < count($this->schOTLdata[$sch]); $i++) {
				if (isset($this->schOTLdata[$sch][$i]['GPOSinfo'])) {
					$newGPOSinfo[$ectr] = $this->schOTLdata[$sch][$i]['GPOSinfo'];
				}
				$newchar_data[$ectr] = array('bidi_class' => $this->schOTLdata[$sch][$i]['bidi_type'], 'uni' => $this->schOTLdata[$sch][$i]['uni']);
				$newgroup .= $this->schOTLdata[$sch][$i]['group'];
				$e.=code2utf($this->schOTLdata[$sch][$i]['uni']);
				if (isset($this->mpdf->CurrentFont['subset'])) {
					$this->mpdf->CurrentFont['subset'][$this->schOTLdata[$sch][$i]['uni']] = $this->schOTLdata[$sch][$i]['uni'];
				}
				$ectr++;
			}
		}
		$this->OTLdata['GPOSinfo'] = $newGPOSinfo;
		$this->OTLdata['char_data'] = $newchar_data;
		$this->OTLdata['group'] = $newgroup;


		// This leaves OTLdata::GPOSinfo, ::bidi_type, & ::group

		return $e;
	}

	function _applyTagSettings($tags, $Features, $omittags = '', $onlytags = false)
	{
		if (empty($this->mpdf->OTLtags['Plus']) && empty($this->mpdf->OTLtags['Minus']) && empty($this->mpdf->OTLtags['FFPlus']) && empty($this->mpdf->OTLtags['FFMinus'])) {
			return $tags;
		}

		// Use $tags as starting point
		$usetags = $tags;

		// Only set / unset tags which are in the font
		// Ignore tags which are in $omittags
		// If $onlytags, then just unset tags which are already in the Tag list

		$fp = $fm = $ffp = $ffm = '';

		// Font features to enable - set by font-variant-xx
		if (isset($this->mpdf->OTLtags['Plus']))
			$fp = $this->mpdf->OTLtags['Plus'];
		preg_match_all('/([a-zA-Z0-9]{4})/', $fp, $m);
		for ($i = 0; $i < count($m[0]); $i++) {
			$t = $m[1][$i];
			// Is it a valid tag?
			if (isset($Features[$t]) && strpos($omittags, $t) === false && (!$onlytags || strpos($tags, $t) !== false )) {
				$usetags .= ' ' . $t;
			}
		}

		// Font features to disable - set by font-variant-xx
		if (isset($this->mpdf->OTLtags['Minus']))
			$fm = $this->mpdf->OTLtags['Minus'];
		preg_match_all('/([a-zA-Z0-9]{4})/', $fm, $m);
		for ($i = 0; $i < count($m[0]); $i++) {
			$t = $m[1][$i];
			// Is it a valid tag?
			if (isset($Features[$t]) && strpos($omittags, $t) === false && (!$onlytags || strpos($tags, $t) !== false )) {
				$usetags = str_replace($t, '', $usetags);
			}
		}

		// Font features to enable - set by font-feature-settings
		if (isset($this->mpdf->OTLtags['FFPlus']))
			$ffp = $this->mpdf->OTLtags['FFPlus']; // Font Features - may include integer: salt4
		preg_match_all('/([a-zA-Z0-9]{4})([\d+]*)/', $ffp, $m);
		for ($i = 0; $i < count($m[0]); $i++) {
			$t = $m[1][$i];
			// Is it a valid tag?
			if (isset($Features[$t]) && strpos($omittags, $t) === false && (!$onlytags || strpos($tags, $t) !== false )) {
				$usetags .= ' ' . $m[0][$i];  //  - may include integer: salt4
			}
		}

		// Font features to disable - set by font-feature-settings
		if (isset($this->mpdf->OTLtags['FFMinus']))
			$ffm = $this->mpdf->OTLtags['FFMinus'];
		preg_match_all('/([a-zA-Z0-9]{4})/', $ffm, $m);
		for ($i = 0; $i < count($m[0]); $i++) {
			$t = $m[1][$i];
			// Is it a valid tag?
			if (isset($Features[$t]) && strpos($omittags, $t) === false && (!$onlytags || strpos($tags, $t) !== false )) {
				$usetags = str_replace($t, '', $usetags);
			}
		}
		return $usetags;
	}

	function _applyGSUBrules($usetags, $scriptTag, $langsys)
	{
		// Features from all Tags are applied together, in Lookup List order.
		// For Indic - should be applied one syllable at a time
		// - Implemented in functions checkContextMatch and checkContextMatchMultiple by failing to match if outside scope of current 'syllable'
		// if $this->restrictToSyllable is true

		$GSUBFeatures = $this->mpdf->CurrentFont['GSUBFeatures'][$scriptTag][$langsys];
		$LookupList = array();
		foreach ($GSUBFeatures AS $tag => $arr) {
			if (strpos($usetags, $tag) !== false) {
				foreach ($arr AS $lu) {
					$LookupList[$lu] = $tag;
				}
			}
		}
		ksort($LookupList);

		foreach ($LookupList AS $lu => $tag) {
			$Type = $this->GSUBLookups[$lu]['Type'];
			$Flag = $this->GSUBLookups[$lu]['Flag'];
			$MarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];
			$tagInt = 1;
			if (preg_match('/' . $tag . '([0-9]{1,2})/', $usetags, $m)) {
				$tagInt = $m[1];
			}
			$ptr = 0;
			// Test each glyph sequentially
			while ($ptr < (count($this->OTLdata))) { // whilst there is another glyph ..0064
				$currGlyph = $this->OTLdata[$ptr]['hex'];
				$currGID = $this->OTLdata[$ptr]['uni'];
				$shift = 1;
				foreach ($this->GSUBLookups[$lu]['Subtables'] AS $c => $subtable_offset) {
					// NB Coverage only looks at glyphs for position 1 (esp. 7.3 and 8.3)
					if (isset($this->GSLuCoverage[$lu][$c][$currGID])) {
						// Get rules from font GSUB subtable
						$shift = $this->_applyGSUBsubtable($lu, $c, $ptr, $currGlyph, $currGID, ($subtable_offset - $this->GSUB_offset), $Type, $Flag, $MarkFilteringSet, $this->GSLuCoverage[$lu][$c], 0, $tag, 0, $tagInt);

						if ($shift) {
							break;
						}
					}
				}
				if ($shift == 0) {
					$shift = 1;
				}
				$ptr += $shift;
			}
		}
	}

	function _applyGSUBrulesSingly($usetags, $scriptTag, $langsys)
	{
		// Features are applied one at a time, working through each codepoint

		$GSUBFeatures = $this->mpdf->CurrentFont['GSUBFeatures'][$scriptTag][$langsys];

		$tags = explode(' ', $usetags);
		foreach ($tags AS $usetag) {
			$LookupList = array();
			foreach ($GSUBFeatures AS $tag => $arr) {
				if (strpos($usetags, $tag) !== false) {
					foreach ($arr AS $lu) {
						$LookupList[$lu] = $tag;
					}
				}
			}
			ksort($LookupList);

			$ptr = 0;
			// Test each glyph sequentially
			while ($ptr < (count($this->OTLdata))) { // whilst there is another glyph ..0064
				$currGlyph = $this->OTLdata[$ptr]['hex'];
				$currGID = $this->OTLdata[$ptr]['uni'];
				$shift = 1;

				foreach ($LookupList AS $lu => $tag) {
					$Type = $this->GSUBLookups[$lu]['Type'];
					$Flag = $this->GSUBLookups[$lu]['Flag'];
					$MarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];
					$tagInt = 1;
					if (preg_match('/' . $tag . '([0-9]{1,2})/', $usetags, $m)) {
						$tagInt = $m[1];
					}

					foreach ($this->GSUBLookups[$lu]['Subtables'] AS $c => $subtable_offset) {
						// NB Coverage only looks at glyphs for position 1 (esp. 7.3 and 8.3)
						if (isset($this->GSLuCoverage[$lu][$c][$currGID])) {
							// Get rules from font GSUB subtable
							$shift = $this->_applyGSUBsubtable($lu, $c, $ptr, $currGlyph, $currGID, ($subtable_offset - $this->GSUB_offset), $Type, $Flag, $MarkFilteringSet, $this->GSLuCoverage[$lu][$c], 0, $tag, 0, $tagInt);

							if ($shift) {
								break 2;
							}
						}
					}
				}
				if ($shift == 0) {
					$shift = 1;
				}
				$ptr += $shift;
			}
		}
	}

	function _applyGSUBrulesMyanmar($usetags, $scriptTag, $langsys)
	{
		// $usetags = locl ccmp rphf pref blwf pstf';
		// applied to all characters

		$GSUBFeatures = $this->mpdf->CurrentFont['GSUBFeatures'][$scriptTag][$langsys];

		// ALL should be applied one syllable at a time
		// Implemented in functions checkContextMatch and checkContextMatchMultiple by failing to match if outside scope of current 'syllable'
		$tags = explode(' ', $usetags);
		foreach ($tags AS $usetag) {

			$LookupList = array();
			foreach ($GSUBFeatures AS $tag => $arr) {
				if ($tag == $usetag) {
					foreach ($arr AS $lu) {
						$LookupList[$lu] = $tag;
					}
				}
			}
			ksort($LookupList);

			foreach ($LookupList AS $lu => $tag) {

				$Type = $this->GSUBLookups[$lu]['Type'];
				$Flag = $this->GSUBLookups[$lu]['Flag'];
				$MarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];
				$tagInt = 1;
				if (preg_match('/' . $tag . '([0-9]{1,2})/', $usetags, $m)) {
					$tagInt = $m[1];
				}

				$ptr = 0;
				// Test each glyph sequentially
				while ($ptr < (count($this->OTLdata))) { // whilst there is another glyph ..0064
					$currGlyph = $this->OTLdata[$ptr]['hex'];
					$currGID = $this->OTLdata[$ptr]['uni'];
					$shift = 1;
					foreach ($this->GSUBLookups[$lu]['Subtables'] AS $c => $subtable_offset) {
						// NB Coverage only looks at glyphs for position 1 (esp. 7.3 and 8.3)
						if (isset($this->GSLuCoverage[$lu][$c][$currGID])) {
							// Get rules from font GSUB subtable
							$shift = $this->_applyGSUBsubtable($lu, $c, $ptr, $currGlyph, $currGID, ($subtable_offset - $this->GSUB_offset), $Type, $Flag, $MarkFilteringSet, $this->GSLuCoverage[$lu][$c], 0, $usetag, 0, $tagInt);

							if ($shift) {
								break;
							}
						}
					}
					if ($shift == 0) {
						$shift = 1;
					}
					$ptr += $shift;
				}
			}
		}
	}

	function _applyGSUBrulesIndic($usetags, $scriptTag, $langsys, $is_old_spec)
	{
		// $usetags = 'locl ccmp nukt akhn rphf rkrf pref blwf half pstf vatu cjct'; then later - init
		// rphf, pref, blwf, half, abvf, pstf, and init are only applied where ['mask'] indicates:  INDIC::FLAG(INDIC::RPHF);
		// The rest are applied to all characters

		$GSUBFeatures = $this->mpdf->CurrentFont['GSUBFeatures'][$scriptTag][$langsys];

		// ALL should be applied one syllable at a time
		// Implemented in functions checkContextMatch and checkContextMatchMultiple by failing to match if outside scope of current 'syllable'
		$tags = explode(' ', $usetags);
		foreach ($tags AS $usetag) {

			$LookupList = array();
			foreach ($GSUBFeatures AS $tag => $arr) {
				if ($tag == $usetag) {
					foreach ($arr AS $lu) {
						$LookupList[$lu] = $tag;
					}
				}
			}
			ksort($LookupList);

			foreach ($LookupList AS $lu => $tag) {

				$Type = $this->GSUBLookups[$lu]['Type'];
				$Flag = $this->GSUBLookups[$lu]['Flag'];
				$MarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];
				$tagInt = 1;
				if (preg_match('/' . $tag . '([0-9]{1,2})/', $usetags, $m)) {
					$tagInt = $m[1];
				}

				$ptr = 0;
				// Test each glyph sequentially
				while ($ptr < (count($this->OTLdata))) { // whilst there is another glyph ..0064
					$currGlyph = $this->OTLdata[$ptr]['hex'];
					$currGID = $this->OTLdata[$ptr]['uni'];
					$shift = 1;
					foreach ($this->GSUBLookups[$lu]['Subtables'] AS $c => $subtable_offset) {
						// NB Coverage only looks at glyphs for position 1 (esp. 7.3 and 8.3)
						if (isset($this->GSLuCoverage[$lu][$c][$currGID])) {
							if (strpos('rphf pref blwf half pstf cfar init', $usetag) !== false) { // only apply when mask indicates
								$mask = 0;
								switch ($usetag) {
									case 'rphf': $mask = (1 << (INDIC::RPHF));
										break;
									case 'pref': $mask = (1 << (INDIC::PREF));
										break;
									case 'blwf': $mask = (1 << (INDIC::BLWF));
										break;
									case 'half': $mask = (1 << (INDIC::HALF));
										break;
									case 'pstf': $mask = (1 << (INDIC::PSTF));
										break;
									case 'cfar': $mask = (1 << (INDIC::CFAR));
										break;
									case 'init': $mask = (1 << (INDIC::INIT));
										break;
								}
								if (!($this->OTLdata[$ptr]['mask'] & $mask)) {
									continue;
								}
							}
							// Get rules from font GSUB subtable
							$shift = $this->_applyGSUBsubtable($lu, $c, $ptr, $currGlyph, $currGID, ($subtable_offset - $this->GSUB_offset), $Type, $Flag, $MarkFilteringSet, $this->GSLuCoverage[$lu][$c], 0, $usetag, $is_old_spec, $tagInt);

							if ($shift) {
								break;
							}
						}

						// Special case for Indic  ZZZ99S
						// Check to substitute Halant-Consonant in PREF, BLWF or PSTF
						// i.e. new spec but GSUB tables have Consonant-Halant in Lookups e.g. FreeSerif, which
						// incorrectly just moved old spec tables to new spec. Uniscribe seems to cope with this
						// See also ttffontsuni.php
						// First check if current glyph is a Halant/Virama
						else if (_OTL_OLD_SPEC_COMPAT_1 && $Type == 4 && !$is_old_spec && strpos('0094D 009CD 00A4D 00ACD 00B4D 00BCD 00C4D 00CCD 00D4D', $currGlyph) !== false) {
							// only apply when 'pref blwf pstf' tags, and when mask indicates
							if (strpos('pref blwf pstf', $usetag) !== false) {
								$mask = 0;
								switch ($usetag) {
									case 'pref': $mask = (1 << (INDIC::PREF));
										break;
									case 'blwf': $mask = (1 << (INDIC::BLWF));
										break;
									case 'pstf': $mask = (1 << (INDIC::PSTF));
										break;
								}
								if (!($this->OTLdata[$ptr]['mask'] & $mask)) {
									continue;
								}

								$nextGlyph = $this->OTLdata[$ptr + 1]['hex'];
								$nextGID = $this->OTLdata[$ptr + 1]['uni'];
								if (isset($this->GSLuCoverage[$lu][$c][$nextGID])) {

									// Get rules from font GSUB subtable
									$shift = $this->_applyGSUBsubtableSpecial($lu, $c, $ptr, $currGlyph, $currGID, $nextGlyph, $nextGID, ($subtable_offset - $this->GSUB_offset), $Type, $this->GSLuCoverage[$lu][$c]);

									if ($shift) {
										break;
									}
								}
							}
						}
					}
					if ($shift == 0) {
						$shift = 1;
					}
					$ptr += $shift;
				}
			}
		}
	}

	function _applyGSUBsubtableSpecial($lookupID, $subtable, $ptr, $currGlyph, $currGID, $nextGlyph, $nextGID, $subtable_offset, $Type, $LuCoverage)
	{

		// Special case for Indic
		// Check to substitute Halant-Consonant in PREF, BLWF or PSTF
		// i.e. new spec but GSUB tables have Consonant-Halant in Lookups e.g. FreeSerif, which
		// incorrectly just moved old spec tables to new spec. Uniscribe seems to cope with this
		// See also ttffontsuni.php

		$this->seek($subtable_offset);
		$SubstFormat = $this->read_ushort();

		// Subtable contains Consonant - Halant
		// Text string contains Halant ($CurrGlyph) - Consonant ($nextGlyph)
		// Halant has already been matched, and already checked that $nextGID is in Coverage table
		////////////////////////////////////////////////////////////////////////////////
		// Only does: LookupType 4: Ligature Substitution Subtable : n to 1
		////////////////////////////////////////////////////////////////////////////////
		$Coverage = $subtable_offset + $this->read_ushort();
		$NextGlyphPos = $LuCoverage[$nextGID];
		$LigSetCount = $this->read_short();

		$this->skip($NextGlyphPos * 2);
		$LigSet = $subtable_offset + $this->read_short();

		$this->seek($LigSet);
		$LigCount = $this->read_short();
		// LigatureSet i.e. all starting with the same Glyph $nextGlyph [Consonant]
		$LigatureOffset = array();
		for ($g = 0; $g < $LigCount; $g++) {
			$LigatureOffset[$g] = $LigSet + $this->read_ushort();
		}
		for ($g = 0; $g < $LigCount; $g++) {
			// Ligature tables
			$this->seek($LigatureOffset[$g]);
			$LigGlyph = $this->read_ushort();
			$substitute = $this->glyphToChar($LigGlyph);
			$CompCount = $this->read_ushort();

			if ($CompCount != 2) {
				return 0;
			} // Only expecting to work with 2:1 (and no ignore characters in between)


			$gid = $this->read_ushort();
			$checkGlyph = $this->glyphToChar($gid); // Other component/input Glyphs starting at position 2 (arrayindex 1)

			if ($currGID == $checkGlyph) {
				$match = true;
			} else {
				$match = false;
				break;
			}

			$GlyphPos = array();
			$GlyphPos[] = $ptr;
			$GlyphPos[] = $ptr + 1;


			if ($match) {
				$shift = $this->GSUBsubstitute($ptr, $substitute, 4, $GlyphPos); // GlyphPos contains positions to set null
				if ($shift)
					return 1;
			}
		}
		return 0;
	}

	function _applyGSUBsubtable($lookupID, $subtable, $ptr, $currGlyph, $currGID, $subtable_offset, $Type, $Flag, $MarkFilteringSet, $LuCoverage, $level = 0, $currentTag, $is_old_spec, $tagInt)
	{
		$ignore = $this->_getGCOMignoreString($Flag, $MarkFilteringSet);

		// Lets start
		$this->seek($subtable_offset);
		$SubstFormat = $this->read_ushort();

		////////////////////////////////////////////////////////////////////////////////
		// LookupType 1: Single Substitution Subtable : 1 to 1
		////////////////////////////////////////////////////////////////////////////////
		if ($Type == 1) {
			// Flag = Ignore
			if ($this->_checkGCOMignore($Flag, $currGlyph, $MarkFilteringSet)) {
				return 0;
			}
			$CoverageOffset = $subtable_offset + $this->read_ushort();
			$GlyphPos = $LuCoverage[$currGID];
			//===========
			// Format 1:
			//===========
			if ($SubstFormat == 1) { // Calculated output glyph indices
				$DeltaGlyphID = $this->read_short();
				$this->seek($CoverageOffset);
				$glyphs = $this->_getCoverageGID();
				$GlyphID = $glyphs[$GlyphPos] + $DeltaGlyphID;
			}
			//===========
			// Format 2:
			//===========
			else if ($SubstFormat == 2) { // Specified output glyph indices
				$GlyphCount = $this->read_ushort();
				$this->skip($GlyphPos * 2);
				$GlyphID = $this->read_ushort();
			}

			$substitute = $this->glyphToChar($GlyphID);
			$shift = $this->GSUBsubstitute($ptr, $substitute, $Type);
			if ($this->debugOTL && $shift) {
				$this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level);
			}
			if ($shift)
				return 1;
			return 0;
		}

		////////////////////////////////////////////////////////////////////////////////
		// LookupType 2: Multiple Substitution Subtable : 1 to n
		////////////////////////////////////////////////////////////////////////////////
		else if ($Type == 2) {
			// Flag = Ignore
			if ($this->_checkGCOMignore($Flag, $currGlyph, $MarkFilteringSet)) {
				return 0;
			}
			$Coverage = $subtable_offset + $this->read_ushort();
			$GlyphPos = $LuCoverage[$currGID];
			$this->skip(2);
			$this->skip($GlyphPos * 2);
			$Sequences = $subtable_offset + $this->read_short();

			$this->seek($Sequences);
			$GlyphCount = $this->read_short();
			$SubstituteGlyphs = array();
			for ($g = 0; $g < $GlyphCount; $g++) {
				$sgid = $this->read_ushort();
				$SubstituteGlyphs[] = $this->glyphToChar($sgid);
			}

			$shift = $this->GSUBsubstitute($ptr, $SubstituteGlyphs, $Type);
			if ($this->debugOTL && $shift) {
				$this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level);
			}
			if ($shift)
				return $shift;
			return 0;
		}
		////////////////////////////////////////////////////////////////////////////////
		// LookupType 3: Alternate Forms : 1 to 1(n)
		////////////////////////////////////////////////////////////////////////////////
		else if ($Type == 3) {
			// Flag = Ignore
			if ($this->_checkGCOMignore($Flag, $currGlyph, $MarkFilteringSet)) {
				return 0;
			}
			$Coverage = $subtable_offset + $this->read_ushort();
			$AlternateSetCount = $this->read_short();
			///////////////////////////////////////////////////////////////////////////////!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
			// Need to set alternate IF set by CSS3 font-feature for a tag
			// i.e. if this is 'salt' alternate may be set to 2
			// default value will be $alt=1 ( === index of 0 in list of alternates)
			$alt = 1; // $alt=1 points to Alternative[0]
			if ($tagInt > 1) {
				$alt = $tagInt;
			}
			///////////////////////////////////////////////////////////////////////////////!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
			if ($alt == 0) {
				return 0;
			} // If specified alternate not present, cancel [ or could default $alt = 1 ?]

			$GlyphPos = $LuCoverage[$currGID];
			$this->skip($GlyphPos * 2);

			$AlternateSets = $subtable_offset + $this->read_short();
			$this->seek($AlternateSets);

			$AlternateGlyphCount = $this->read_short();
			if ($alt > $AlternateGlyphCount) {
				return 0;
			} // If specified alternate not present, cancel [ or could default $alt = 1 ?]

			$this->skip(($alt - 1) * 2);
			$GlyphID = $this->read_ushort();

			$substitute = $this->glyphToChar($GlyphID);
			$shift = $this->GSUBsubstitute($ptr, $substitute, $Type);
			if ($this->debugOTL && $shift) {
				$this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level);
			}
			if ($shift)
				return 1;
			return 0;
		}
		////////////////////////////////////////////////////////////////////////////////
		// LookupType 4: Ligature Substitution Subtable : n to 1
		////////////////////////////////////////////////////////////////////////////////
		else if ($Type == 4) {
			// Flag = Ignore
			if ($this->_checkGCOMignore($Flag, $currGlyph, $MarkFilteringSet)) {
				return 0;
			}
			$Coverage = $subtable_offset + $this->read_ushort();
			$FirstGlyphPos = $LuCoverage[$currGID];

			$LigSetCount = $this->read_short();

			$this->skip($FirstGlyphPos * 2);
			$LigSet = $subtable_offset + $this->read_short();

			$this->seek($LigSet);
			$LigCount = $this->read_short();
			// LigatureSet i.e. all starting with the same first Glyph $currGlyph
			$LigatureOffset = array();
			for ($g = 0; $g < $LigCount; $g++) {
				$LigatureOffset[$g] = $LigSet + $this->read_ushort();
			}
			for ($g = 0; $g < $LigCount; $g++) {
				// Ligature tables
				$this->seek($LigatureOffset[$g]);
				$LigGlyph = $this->read_ushort(); // Output Ligature GlyphID
				$substitute = $this->glyphToChar($LigGlyph);
				$CompCount = $this->read_ushort();

				$spos = $ptr;
				$match = true;
				$GlyphPos = array();
				$GlyphPos[] = $spos;
				for ($l = 1; $l < $CompCount; $l++) {
					$gid = $this->read_ushort();
					$checkGlyph = $this->glyphToChar($gid); // Other component/input Glyphs starting at position 2 (arrayindex 1)

					$spos++;
					//while $this->OTLdata[$spos]['uni'] is an "ignore" =>  spos++
					while (isset($this->OTLdata[$spos]) && strpos($ignore, $this->OTLdata[$spos]['hex']) !== false) {
						$spos++;
					}

					if (isset($this->OTLdata[$spos]) && $this->OTLdata[$spos]['uni'] == $checkGlyph) {
						$GlyphPos[] = $spos;
					} else {
						$match = false;
						break;
					}
				}


				if ($match) {
					$shift = $this->GSUBsubstitute($ptr, $substitute, $Type, $GlyphPos); // GlyphPos contains positions to set null
					if ($this->debugOTL && $shift) {
						$this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level);
					}
					if ($shift)
						return ($spos - $ptr + 1 - ($CompCount - 1));
				}
			}
			return 0;
		}

		////////////////////////////////////////////////////////////////////////////////
		// LookupType 5: Contextual Substitution Subtable
		////////////////////////////////////////////////////////////////////////////////
		else if ($Type == 5) {
			//===========
			// Format 1: Simple Context Glyph Substitution
			//===========
			if ($SubstFormat == 1) {
				$CoverageTableOffset = $subtable_offset + $this->read_ushort();
				$SubRuleSetCount = $this->read_ushort();
				$SubRuleSetOffset = array();
				for ($b = 0; $b < $SubRuleSetCount; $b++) {
					$offset = $this->read_ushort();
					if ($offset == 0x0000) {
						$SubRuleSetOffset[] = $offset;
					} else {
						$SubRuleSetOffset[] = $subtable_offset + $offset;
					}
				}

				// SubRuleSet tables: All contexts beginning with the same glyph
				// Select the SubRuleSet required using the position of the glyph in the coverage table
				$GlyphPos = $LuCoverage[$currGID];
				if ($SubRuleSetOffset[$GlyphPos] > 0) {
					$this->seek($SubRuleSetOffset[$GlyphPos]);
					$SubRuleCnt = $this->read_ushort();
					$SubRule = array();
					for ($b = 0; $b < $SubRuleCnt; $b++) {
						$SubRule[$b] = $SubRuleSetOffset[$GlyphPos] + $this->read_ushort();
					}
					for ($b = 0; $b < $SubRuleCnt; $b++) {  // EACH RULE
						$this->seek($SubRule[$b]);
						$InputGlyphCount = $this->read_ushort();
						$SubstCount = $this->read_ushort();

						$Backtrack = array();
						$Lookahead = array();
						$Input = array();
						$Input[0] = $this->OTLdata[$ptr]['uni'];
						for ($r = 1; $r < $InputGlyphCount; $r++) {
							$gid = $this->read_ushort();
							$Input[$r] = $this->glyphToChar($gid);
						}
						$matched = $this->checkContextMatch($Input, $Backtrack, $Lookahead, $ignore, $ptr);
						if ($matched) {
							if ($this->debugOTL) {
								$this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level);
							}
							for ($p = 0; $p < $SubstCount; $p++) { // EACH LOOKUP
								$SequenceIndex[$p] = $this->read_ushort();
								$LookupListIndex[$p] = $this->read_ushort();
							}

							for ($p = 0; $p < $SubstCount; $p++) {
								// Apply  $LookupListIndex  at   $SequenceIndex
								if ($SequenceIndex[$p] >= $InputGlyphCount) {
									continue;
								}
								$lu = $LookupListIndex[$p];
								$luType = $this->GSUBLookups[$lu]['Type'];
								$luFlag = $this->GSUBLookups[$lu]['Flag'];
								$luMarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];

								$luptr = $matched[$SequenceIndex[$p]];
								$lucurrGlyph = $this->OTLdata[$luptr]['hex'];
								$lucurrGID = $this->OTLdata[$luptr]['uni'];

								foreach ($this->GSUBLookups[$lu]['Subtables'] AS $luc => $lusubtable_offset) {
									$shift = $this->_applyGSUBsubtable($lu, $luc, $luptr, $lucurrGlyph, $lucurrGID, ($lusubtable_offset - $this->GSUB_offset), $luType, $luFlag, $luMarkFilteringSet, $this->GSLuCoverage[$lu][$luc], 1, $currentTag, $is_old_spec, $tagInt);
									if ($shift) {
										break;
									}
								}
							}

							if (!defined("OMIT_OTL_FIX_3") || OMIT_OTL_FIX_3 != 1) {
								return $shift;
							} /* OTL_FIX_3 */
							else
								return $InputGlyphCount; // should be + matched ignores in Input Sequence
						}
					}
				}
				return 0;
			}

			//===========
			// Format 2:
			//===========
			// Format 2: Class-based Context Glyph Substitution
			else if ($SubstFormat == 2) {

				$CoverageTableOffset = $subtable_offset + $this->read_ushort();
				$InputClassDefOffset = $subtable_offset + $this->read_ushort();
				$SubClassSetCnt = $this->read_ushort();
				$SubClassSetOffset = array();
				for ($b = 0; $b < $SubClassSetCnt; $b++) {
					$offset = $this->read_ushort();
					if ($offset == 0x0000) {
						$SubClassSetOffset[] = $offset;
					} else {
						$SubClassSetOffset[] = $subtable_offset + $offset;
					}
				}

				$InputClasses = $this->_getClasses($InputClassDefOffset);

				for ($s = 0; $s < $SubClassSetCnt; $s++) { // $SubClassSet is ordered by input class-may be NULL
					// Select $SubClassSet if currGlyph is in First Input Class
					if ($SubClassSetOffset[$s] > 0 && isset($InputClasses[$s][$currGID])) {
						$this->seek($SubClassSetOffset[$s]);
						$SubClassRuleCnt = $this->read_ushort();
						$SubClassRule = array();
						for ($b = 0; $b < $SubClassRuleCnt; $b++) {
							$SubClassRule[$b] = $SubClassSetOffset[$s] + $this->read_ushort();
						}

						for ($b = 0; $b < $SubClassRuleCnt; $b++) {  // EACH RULE
							$this->seek($SubClassRule[$b]);
							$InputGlyphCount = $this->read_ushort();
							$SubstCount = $this->read_ushort();
							$Input = array();
							for ($r = 1; $r < $InputGlyphCount; $r++) {
								$Input[$r] = $this->read_ushort();
							}

							$inputClass = $s;

							$inputGlyphs = array();
							$inputGlyphs[0] = $InputClasses[$inputClass];

							if ($InputGlyphCount > 1) {
								//  NB starts at 1
								for ($gcl = 1; $gcl < $InputGlyphCount; $gcl++) {
									$classindex = $Input[$gcl];
									if (isset($InputClasses[$classindex])) {
										$inputGlyphs[$gcl] = $InputClasses[$classindex];
									} else {
										$inputGlyphs[$gcl] = '';
									}
								}
							}

							// Class 0 contains all the glyphs NOT in the other classes
							$class0excl = array();
							for ($gc = 1; $gc <= count($InputClasses); $gc++) {
								if (is_array($InputClasses[$gc]))
									$class0excl = $class0excl + $InputClasses[$gc];
							}

							$backtrackGlyphs = array();
							$lookaheadGlyphs = array();

							$matched = $this->checkContextMatchMultipleUni($inputGlyphs, $backtrackGlyphs, $lookaheadGlyphs, $ignore, $ptr, $class0excl);
							if ($matched) {
								if ($this->debugOTL) {
									$this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level);
								}
								for ($p = 0; $p < $SubstCount; $p++) { // EACH LOOKUP
									$SequenceIndex[$p] = $this->read_ushort();
									$LookupListIndex[$p] = $this->read_ushort();
								}

								for ($p = 0; $p < $SubstCount; $p++) {
									// Apply  $LookupListIndex  at   $SequenceIndex
									if ($SequenceIndex[$p] >= $InputGlyphCount) {
										continue;
									}
									$lu = $LookupListIndex[$p];
									$luType = $this->GSUBLookups[$lu]['Type'];
									$luFlag = $this->GSUBLookups[$lu]['Flag'];
									$luMarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];

									$luptr = $matched[$SequenceIndex[$p]];
									$lucurrGlyph = $this->OTLdata[$luptr]['hex'];
									$lucurrGID = $this->OTLdata[$luptr]['uni'];

									foreach ($this->GSUBLookups[$lu]['Subtables'] AS $luc => $lusubtable_offset) {
										$shift = $this->_applyGSUBsubtable($lu, $luc, $luptr, $lucurrGlyph, $lucurrGID, ($lusubtable_offset - $this->GSUB_offset), $luType, $luFlag, $luMarkFilteringSet, $this->GSLuCoverage[$lu][$luc], 1, $currentTag, $is_old_spec, $tagInt);
										if ($shift) {
											break;
										}
									}
								}

								if (!defined("OMIT_OTL_FIX_3") || OMIT_OTL_FIX_3 != 1) {
									return $shift;
								} /* OTL_FIX_3 */
								else
									return $InputGlyphCount; // should be + matched ignores in Input Sequence
							}
						}
					}
				}

				return 0;
			}

			//===========
			// Format 3:
			//===========
			// Format 3: Coverage-based Context Glyph Substitution
			else if ($SubstFormat == 3) {
				throw new MpdfException("GSUB Lookup Type " . $Type . " Format " . $SubstFormat . " not TESTED YET.");
			}
		}

		////////////////////////////////////////////////////////////////////////////////
		// LookupType 6: Chaining Contextual Substitution Subtable
		////////////////////////////////////////////////////////////////////////////////
		else if ($Type == 6) {

			//===========
			// Format 1:
			//===========
			// Format 1: Simple Chaining Context Glyph Substitution
			if ($SubstFormat == 1) {
				$Coverage = $subtable_offset + $this->read_ushort();
				$GlyphPos = $LuCoverage[$currGID];
				$ChainSubRuleSetCount = $this->read_ushort();
				// All of the ChainSubRule tables defining contexts that begin with the same first glyph are grouped together and defined in a ChainSubRuleSet table
				$this->skip($GlyphPos * 2);
				$ChainSubRuleSet = $subtable_offset + $this->read_ushort();
				$this->seek($ChainSubRuleSet);
				$ChainSubRuleCount = $this->read_ushort();

				for ($s = 0; $s < $ChainSubRuleCount; $s++) {
					$ChainSubRule[$s] = $ChainSubRuleSet + $this->read_ushort();
				}

				for ($s = 0; $s < $ChainSubRuleCount; $s++) {
					$this->seek($ChainSubRule[$s]);

					$BacktrackGlyphCount = $this->read_ushort();
					$Backtrack = array();
					for ($b = 0; $b < $BacktrackGlyphCount; $b++) {
						$gid = $this->read_ushort();
						$Backtrack[] = $this->glyphToChar($gid);
					}
					$Input = array();
					$Input[0] = $this->OTLdata[$ptr]['uni'];
					$InputGlyphCount = $this->read_ushort();
					for ($b = 1; $b < $InputGlyphCount; $b++) {
						$gid = $this->read_ushort();
						$Input[$b] = $this->glyphToChar($gid);
					}
					$LookaheadGlyphCount = $this->read_ushort();
					$Lookahead = array();
					for ($b = 0; $b < $LookaheadGlyphCount; $b++) {
						$gid = $this->read_ushort();
						$Lookahead[] = $this->glyphToChar($gid);
					}

					$matched = $this->checkContextMatch($Input, $Backtrack, $Lookahead, $ignore, $ptr);
					if ($matched) {
						if ($this->debugOTL) {
							$this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level);
						}
						$SubstCount = $this->read_ushort();
						for ($p = 0; $p < $SubstCount; $p++) {
							// SubstLookupRecord
							$SubstLookupRecord[$p]['SequenceIndex'] = $this->read_ushort();
							$SubstLookupRecord[$p]['LookupListIndex'] = $this->read_ushort();
						}
						for ($p = 0; $p < $SubstCount; $p++) {
							// Apply  $SubstLookupRecord[$p]['LookupListIndex']  at   $SubstLookupRecord[$p]['SequenceIndex']
							if ($SubstLookupRecord[$p]['SequenceIndex'] >= $InputGlyphCount) {
								continue;
							}
							$lu = $SubstLookupRecord[$p]['LookupListIndex'];
							$luType = $this->GSUBLookups[$lu]['Type'];
							$luFlag = $this->GSUBLookups[$lu]['Flag'];
							$luMarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];

							$luptr = $matched[$SubstLookupRecord[$p]['SequenceIndex']];
							$lucurrGlyph = $this->OTLdata[$luptr]['hex'];
							$lucurrGID = $this->OTLdata[$luptr]['uni'];

							foreach ($this->GSUBLookups[$lu]['Subtables'] AS $luc => $lusubtable_offset) {
								$shift = $this->_applyGSUBsubtable($lu, $luc, $luptr, $lucurrGlyph, $lucurrGID, ($lusubtable_offset - $this->GSUB_offset), $luType, $luFlag, $luMarkFilteringSet, $this->GSLuCoverage[$lu][$luc], 1, $currentTag, $is_old_spec, $tagInt);
								if ($shift) {
									break;
								}
							}
						}
						if (!defined("OMIT_OTL_FIX_3") || OMIT_OTL_FIX_3 != 1) {
							return $shift;
						} /* OTL_FIX_3 */
						else
							return $InputGlyphCount; // should be + matched ignores in Input Sequence
					}
				}
				return 0;
			}

			//===========
			// Format 2:
			//===========
			// Format 2: Class-based Chaining Context Glyph Substitution  p257
			else if ($SubstFormat == 2) {

				// NB Format 2 specifies fixed class assignments (identical for each position in the backtrack, input, or lookahead sequence) and exclusive classes (a glyph cannot be in more than one class at a time)

				$CoverageTableOffset = $subtable_offset + $this->read_ushort();
				$BacktrackClassDefOffset = $subtable_offset + $this->read_ushort();
				$InputClassDefOffset = $subtable_offset + $this->read_ushort();
				$LookaheadClassDefOffset = $subtable_offset + $this->read_ushort();
				$ChainSubClassSetCnt = $this->read_ushort();
				$ChainSubClassSetOffset = array();
				for ($b = 0; $b < $ChainSubClassSetCnt; $b++) {
					$offset = $this->read_ushort();
					if ($offset == 0x0000) {
						$ChainSubClassSetOffset[] = $offset;
					} else {
						$ChainSubClassSetOffset[] = $subtable_offset + $offset;
					}
				}

				$BacktrackClasses = $this->_getClasses($BacktrackClassDefOffset);
				$InputClasses = $this->_getClasses($InputClassDefOffset);
				$LookaheadClasses = $this->_getClasses($LookaheadClassDefOffset);

				for ($s = 0; $s < $ChainSubClassSetCnt; $s++) { // $ChainSubClassSet is ordered by input class-may be NULL
					// Select $ChainSubClassSet if currGlyph is in First Input Class
					if ($ChainSubClassSetOffset[$s] > 0 && isset($InputClasses[$s][$currGID])) {
						$this->seek($ChainSubClassSetOffset[$s]);
						$ChainSubClassRuleCnt = $this->read_ushort();
						$ChainSubClassRule = array();
						for ($b = 0; $b < $ChainSubClassRuleCnt; $b++) {
							$ChainSubClassRule[$b] = $ChainSubClassSetOffset[$s] + $this->read_ushort();
						}

						for ($b = 0; $b < $ChainSubClassRuleCnt; $b++) {  // EACH RULE
							$this->seek($ChainSubClassRule[$b]);
							$BacktrackGlyphCount = $this->read_ushort();
							for ($r = 0; $r < $BacktrackGlyphCount; $r++) {
								$Backtrack[$r] = $this->read_ushort();
							}
							$InputGlyphCount = $this->read_ushort();
							for ($r = 1; $r < $InputGlyphCount; $r++) {
								$Input[$r] = $this->read_ushort();
							}
							$LookaheadGlyphCount = $this->read_ushort();
							for ($r = 0; $r < $LookaheadGlyphCount; $r++) {
								$Lookahead[$r] = $this->read_ushort();
							}


							// These contain classes of glyphs as arrays
							// $InputClasses[(class)] e.g. 0x02E6,0x02E7,0x02E8
							// $LookaheadClasses[(class)]
							// $BacktrackClasses[(class)]
							// These contain arrays of classIndexes
							// [Backtrack] [Lookahead] and [Input] (Input is from the second position only)


							$inputClass = $s; //???

							$inputGlyphs = array();
							$inputGlyphs[0] = $InputClasses[$inputClass];

							if ($InputGlyphCount > 1) {
								//  NB starts at 1
								for ($gcl = 1; $gcl < $InputGlyphCount; $gcl++) {
									$classindex = $Input[$gcl];
									if (isset($InputClasses[$classindex])) {
										$inputGlyphs[$gcl] = $InputClasses[$classindex];
									} else {
										$inputGlyphs[$gcl] = '';
									}
								}
							}

							// Class 0 contains all the glyphs NOT in the other classes
							$class0excl = array();
							for ($gc = 1; $gc <= count($InputClasses); $gc++) {
								if (isset($InputClasses[$gc]))
									$class0excl = $class0excl + $InputClasses[$gc];
							}

							if ($BacktrackGlyphCount) {
								for ($gcl = 0; $gcl < $BacktrackGlyphCount; $gcl++) {
									$classindex = $Backtrack[$gcl];
									if (isset($BacktrackClasses[$classindex])) {
										$backtrackGlyphs[$gcl] = $BacktrackClasses[$classindex];
									} else {
										$backtrackGlyphs[$gcl] = '';
									}
								}
							} else {
								$backtrackGlyphs = array();
							}

							// Class 0 contains all the glyphs NOT in the other classes
							$bclass0excl = array();
							for ($gc = 1; $gc <= count($BacktrackClasses); $gc++) {
								if (isset($BacktrackClasses[$gc]))
									$bclass0excl = $bclass0excl + $BacktrackClasses[$gc];
							}


							if ($LookaheadGlyphCount) {
								for ($gcl = 0; $gcl < $LookaheadGlyphCount; $gcl++) {
									$classindex = $Lookahead[$gcl];
									if (isset($LookaheadClasses[$classindex])) {
										$lookaheadGlyphs[$gcl] = $LookaheadClasses[$classindex];
									} else {
										$lookaheadGlyphs[$gcl] = '';
									}
								}
							} else {
								$lookaheadGlyphs = array();
							}

							// Class 0 contains all the glyphs NOT in the other classes
							$lclass0excl = array();
							for ($gc = 1; $gc <= count($LookaheadClasses); $gc++) {
								if (isset($LookaheadClasses[$gc]))
									$lclass0excl = $lclass0excl + $LookaheadClasses[$gc];
							}


							$matched = $this->checkContextMatchMultipleUni($inputGlyphs, $backtrackGlyphs, $lookaheadGlyphs, $ignore, $ptr, $class0excl, $bclass0excl, $lclass0excl);
							if ($matched) {
								if ($this->debugOTL) {
									$this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level);
								}
								$SubstCount = $this->read_ushort();
								for ($p = 0; $p < $SubstCount; $p++) { // EACH LOOKUP
									$SequenceIndex[$p] = $this->read_ushort();
									$LookupListIndex[$p] = $this->read_ushort();
								}

								for ($p = 0; $p < $SubstCount; $p++) {
									// Apply  $LookupListIndex  at   $SequenceIndex
									if ($SequenceIndex[$p] >= $InputGlyphCount) {
										continue;
									}
									$lu = $LookupListIndex[$p];
									$luType = $this->GSUBLookups[$lu]['Type'];
									$luFlag = $this->GSUBLookups[$lu]['Flag'];
									$luMarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];

									$luptr = $matched[$SequenceIndex[$p]];
									$lucurrGlyph = $this->OTLdata[$luptr]['hex'];
									$lucurrGID = $this->OTLdata[$luptr]['uni'];

									foreach ($this->GSUBLookups[$lu]['Subtables'] AS $luc => $lusubtable_offset) {
										$shift = $this->_applyGSUBsubtable($lu, $luc, $luptr, $lucurrGlyph, $lucurrGID, ($lusubtable_offset - $this->GSUB_offset), $luType, $luFlag, $luMarkFilteringSet, $this->GSLuCoverage[$lu][$luc], 1, $currentTag, $is_old_spec, $tagInt);
										if ($shift) {
											break;
										}
									}
								}

								if (!defined("OMIT_OTL_FIX_3") || OMIT_OTL_FIX_3 != 1) {
									return $shift;
								} /* OTL_FIX_3 */
								else
									return $InputGlyphCount; // should be + matched ignores in Input Sequence
							}
						}
					}
				}

				return 0;
			}

			//===========
			// Format 3:
			//===========
			// Format 3: Coverage-based Chaining Context Glyph Substitution  p259
			else if ($SubstFormat == 3) {

				$BacktrackGlyphCount = $this->read_ushort();
				for ($b = 0; $b < $BacktrackGlyphCount; $b++) {
					$CoverageBacktrackOffset[] = $subtable_offset + $this->read_ushort(); // in glyph sequence order
				}
				$InputGlyphCount = $this->read_ushort();
				for ($b = 0; $b < $InputGlyphCount; $b++) {
					$CoverageInputOffset[] = $subtable_offset + $this->read_ushort(); // in glyph sequence order
				}
				$LookaheadGlyphCount = $this->read_ushort();
				for ($b = 0; $b < $LookaheadGlyphCount; $b++) {
					$CoverageLookaheadOffset[] = $subtable_offset + $this->read_ushort(); // in glyph sequence order
				}
				$SubstCount = $this->read_ushort();
				$save_pos = $this->_pos; // Save the point just after PosCount

				$CoverageBacktrackGlyphs = array();
				for ($b = 0; $b < $BacktrackGlyphCount; $b++) {
					$this->seek($CoverageBacktrackOffset[$b]);
					$glyphs = $this->_getCoverage();
					$CoverageBacktrackGlyphs[$b] = implode("|", $glyphs);
				}
				$CoverageInputGlyphs = array();
				for ($b = 0; $b < $InputGlyphCount; $b++) {
					$this->seek($CoverageInputOffset[$b]);
					$glyphs = $this->_getCoverage();
					$CoverageInputGlyphs[$b] = implode("|", $glyphs);
				}
				$CoverageLookaheadGlyphs = array();
				for ($b = 0; $b < $LookaheadGlyphCount; $b++) {
					$this->seek($CoverageLookaheadOffset[$b]);
					$glyphs = $this->_getCoverage();
					$CoverageLookaheadGlyphs[$b] = implode("|", $glyphs);
				}

				$matched = $this->checkContextMatchMultiple($CoverageInputGlyphs, $CoverageBacktrackGlyphs, $CoverageLookaheadGlyphs, $ignore, $ptr);
				if ($matched) {
					if ($this->debugOTL) {
						$this->_dumpproc('GSUB', $lookupID, $subtable, $Type, $SubstFormat, $ptr, $currGlyph, $level);
					}

					$this->seek($save_pos); // Return to just after PosCount
					for ($p = 0; $p < $SubstCount; $p++) {
						// SubstLookupRecord
						$SubstLookupRecord[$p]['SequenceIndex'] = $this->read_ushort();
						$SubstLookupRecord[$p]['LookupListIndex'] = $this->read_ushort();
					}
					for ($p = 0; $p < $SubstCount; $p++) {
						// Apply  $SubstLookupRecord[$p]['LookupListIndex']  at   $SubstLookupRecord[$p]['SequenceIndex']
						if ($SubstLookupRecord[$p]['SequenceIndex'] >= $InputGlyphCount) {
							continue;
						}
						$lu = $SubstLookupRecord[$p]['LookupListIndex'];
						$luType = $this->GSUBLookups[$lu]['Type'];
						$luFlag = $this->GSUBLookups[$lu]['Flag'];
						$luMarkFilteringSet = $this->GSUBLookups[$lu]['MarkFilteringSet'];

						$luptr = $matched[$SubstLookupRecord[$p]['SequenceIndex']];
						$lucurrGlyph = $this->OTLdata[$luptr]['hex'];
						$lucurrGID = $this->OTLdata[$luptr]['uni'];

						foreach ($this->GSUBLookups[$lu]['Subtables'] AS $luc => $lusubtable_offset) {
							$shift = $this->_applyGSUBsubtable($lu, $luc, $luptr, $lucurrGlyph, $lucurrGID, ($lusubtable_offset - $this->GSUB_offset), $luType, $luFlag, $luMarkFilteringSet, $this->GSLuCoverage[$lu][$luc], 1, $currentTag, $is_old_spec, $tagInt);
							if ($shift) {
								break;
							}
						}
					}
					if (!defined("OMIT_OTL_FIX_3") || OMIT_OTL_FIX_3 != 1) {
						return (isset($shift) ? $shift : 0);
					} /* OTL_FIX_3 */
					else
						return $InputGlyphCount; // should be + matched ignores in Input Sequence
				}

				return 0;
			}
		}

		else {
			throw new MpdfException("GSUB Lookup Type " . $Type . " not supported.");
		}
	}

	function _updateLigatureMarks($pos, $n)
	{
		if ($n > 0) {
			// Update position of Ligatures and associated Marks
			// Foreach lig/assocMarks
			// Any position lpos or mpos > $pos + count($substitute)
			//  $this->assocMarks = array();    // assocMarks[$pos mpos] => array(compID, ligPos)
			//  $this->assocLigs = array(); // Ligatures[$pos lpos] => nc
			for ($p = count($this->OTLdata) - 1; $p >= ($pos + $n); $p--) {
				if (isset($this->assocLigs[$p])) {
					$tmp = $this->assocLigs[$p];
					unset($this->assocLigs[$p]);
					$this->assocLigs[($p + $n)] = $tmp;
				}
			}
			for ($p = count($this->OTLdata) - 1; $p >= 0; $p--) {
				if (isset($this->assocMarks[$p])) {
					if ($this->assocMarks[$p]['ligPos'] >= ($pos + $n)) {
						$this->assocMarks[$p]['ligPos'] += $n;
					}
					if ($p >= ($pos + $n)) {
						$tmp = $this->assocMarks[$p];
						unset($this->assocMarks[$p]);
						$this->assocMarks[($p + $n)] = $tmp;
					}
				}
			}
		} else if ($n < 1) { // glyphs removed
			$nrem = -$n;
			// Update position of pre-existing Ligatures and associated Marks
			for ($p = ($pos + 1); $p < count($this->OTLdata); $p++) {
				if (isset($this->assocLigs[$p])) {
					$tmp = $this->assocLigs[$p];
					unset($this->assocLigs[$p]);
					$this->assocLigs[($p - $nrem)] = $tmp;
				}
			}
			for ($p = 0; $p < count($this->OTLdata); $p++) {
				if (isset($this->assocMarks[$p])) {
					if ($this->assocMarks[$p]['ligPos'] >= ($pos)) {
						$this->assocMarks[$p]['ligPos'] -= $nrem;
					}
					if ($p > $pos) {
						$tmp = $this->assocMarks[$p];
						unset($this->assocMarks[$p]);
						$this->assocMarks[($p - $nrem)] = $tmp;
					}
				}
			}
		}
	}

	function GSUBsubstitute($pos, $substitute, $Type, $GlyphPos = NULL)
	{

		// LookupType 1: Simple Substitution Subtable : 1 to 1
		// LookupType 3: Alternate Forms : 1 to 1(n)
		if ($Type == 1 || $Type == 3) {
			$this->OTLdata[$pos]['uni'] = $substitute;
			$this->OTLdata[$pos]['hex'] = $this->unicode_hex($substitute);
			return 1;
		}
		// LookupType 2: Multiple Substitution Subtable : 1 to n
		else if ($Type == 2) {
			for ($i = 0; $i < count($substitute); $i++) {
				$uni = $substitute[$i];
				$newOTLdata[$i] = array();
				$newOTLdata[$i]['uni'] = $uni;
				$newOTLdata[$i]['hex'] = $this->unicode_hex($uni);


				// Get types of new inserted chars - or replicate type of char being replaced
				//  $bt = UCDN::get_bidi_class($uni);
				//  if (!$bt) {
				$bt = $this->OTLdata[$pos]['bidi_type'];
				//  }

				if (strpos($this->GlyphClassMarks, $newOTLdata[$i]['hex']) !== false) {
					$gp = 'M';
				} else if ($uni == 32) {
					$gp = 'S';
				} else {
					$gp = 'C';
				}

				// Need to update matra_type ??? of new glyphs inserted ???????????????????????????????????????

				$newOTLdata[$i]['bidi_type'] = $bt;
				$newOTLdata[$i]['group'] = $gp;

				// Need to update details of new glyphs inserted
				$newOTLdata[$i]['general_category'] = $this->OTLdata[$pos]['general_category'];

				if ($this->shaper == 'I' || $this->shaper == 'K' || $this->shaper == 'S') {
					$newOTLdata[$i]['indic_category'] = $this->OTLdata[$pos]['indic_category'];
					$newOTLdata[$i]['indic_position'] = $this->OTLdata[$pos]['indic_position'];
				} else if ($this->shaper == 'M') {
					$newOTLdata[$i]['myanmar_category'] = $this->OTLdata[$pos]['myanmar_category'];
					$newOTLdata[$i]['myanmar_position'] = $this->OTLdata[$pos]['myanmar_position'];
				}
				if (isset($this->OTLdata[$pos]['mask'])) {
					$newOTLdata[$i]['mask'] = $this->OTLdata[$pos]['mask'];
				}
				if (isset($this->OTLdata[$pos]['syllable'])) {
					$newOTLdata[$i]['syllable'] = $this->OTLdata[$pos]['syllable'];
				}
			}
			if ($this->shaper == 'K' || $this->shaper == 'T' || $this->shaper == 'L') {
				if ($this->OTLdata[$pos]['wordend']) {
					$newOTLdata[count($substitute) - 1]['wordend'] = true;
				}
			}

			array_splice($this->OTLdata, $pos, 1, $newOTLdata); // Replace 1 with n
			// Update position of Ligatures and associated Marks
			// count($substitute)-1  is the number of glyphs added
			$nadd = count($substitute) - 1;
			$this->_updateLigatureMarks($pos, $nadd);
			return count($substitute);
		}
		// LookupType 4: Ligature Substitution Subtable : n to 1
		else if ($Type == 4) {
			// Create Ligatures and associated Marks
			$firstGlyph = $this->OTLdata[$pos]['hex'];

			// If all components of the ligature are marks (and in the same syllable), we call this a mark ligature.
			$contains_marks = false;
			$contains_nonmarks = false;
			if (isset($this->OTLdata[$pos]['syllable'])) {
				$current_syllable = $this->OTLdata[$pos]['syllable'];
			} else {
				$current_syllable = 0;
			}
			for ($i = 0; $i < count($GlyphPos); $i++) {
				// If subsequent components are not Marks as well - don't ligate
				$unistr = $this->OTLdata[$GlyphPos[$i]]['hex'];
				if ($this->restrictToSyllable && isset($this->OTLdata[$GlyphPos[$i]]['syllable']) && $this->OTLdata[$GlyphPos[$i]]['syllable'] != $current_syllable) {
					return 0;
				}
				if (strpos($this->GlyphClassMarks, $unistr) !== false) {
					$contains_marks = true;
				} else {
					$contains_nonmarks = true;
				}
			}
			if ($contains_marks && !$contains_nonmarks) {
				// Mark Ligature (all components are Marks)
				$firstMarkAssoc = '';
				if (isset($this->assocMarks[$pos])) {
					$firstMarkAssoc = $this->assocMarks[$pos];
				}
				// If all components of the ligature are marks, we call this a mark ligature.
				for ($i = 1; $i < count($GlyphPos); $i++) {

					// If subsequent components are not Marks as well - don't ligate
					//      $unistr = $this->OTLdata[$GlyphPos[$i]]['hex'];
					//      if (strpos($this->GlyphClassMarks, $unistr )===false) { return; }

					$nextMarkAssoc = '';
					if (isset($this->assocMarks[$GlyphPos[$i]])) {
						$nextMarkAssoc = $this->assocMarks[$GlyphPos[$i]];
					}
					// If first component was attached to a previous ligature component,
					// all subsequent components should be attached to the same ligature
					// component, otherwise we shouldn't ligate them.
					// If first component was NOT attached to a previous ligature component,
					// all subsequent components should also NOT be attached to any ligature component,
					if ($firstMarkAssoc != $nextMarkAssoc) {
						// unless they are attached to the first component itself!
						//          if (!is_array($nextMarkAssoc) || $nextMarkAssoc['ligPos']!= $pos) { return; }
						// Update/Edit - In test with myanmartext font
						// &#x1004;&#x103a;&#x1039;&#x1000;&#x1039;&#x1000;&#x103b;&#x103c;&#x103d;&#x1031;&#x102d;
						// => Lookup 17  E003 E066B E05A 102D
						// E003 and 102D should form a mark ligature, but 102D is already associated with (non-mark) ligature E05A
						// So instead of disallowing the mark ligature to form, just dissociate...
						if (!is_array($nextMarkAssoc) || $nextMarkAssoc['ligPos'] != $pos) {
							unset($this->assocMarks[$GlyphPos[$i]]);
						}
					}
				}

				/*
				 * - If it *is* a mark ligature, we don't allocate a new ligature id, and leave
				 *   the ligature to keep its old ligature id.  This will allow it to attach to
				 *   a base ligature in GPOS.  Eg. if the sequence is: LAM,LAM,SHADDA,FATHA,HEH,
				 *   and LAM,LAM,HEH form a ligature, they will leave SHADDA and FATHA wit a
				 *   ligature id and component value of 2.  Then if SHADDA,FATHA form a ligature
				 *   later, we don't want them to lose their ligature id/component, otherwise
				 *   GPOS will fail to correctly position the mark ligature on top of the
				 *   LAM,LAM,HEH ligature.
				 */
				// So if is_array($firstMarkAssoc) - the new (Mark) ligature should keep this association

				$lastPos = $GlyphPos[(count($GlyphPos) - 1)];
			} else {
				/*
				 * - Ligatures cannot be formed across glyphs attached to different components
				 *   of previous ligatures.  Eg. the sequence is LAM,SHADDA,LAM,FATHA,HEH, and
				 *   LAM,LAM,HEH form a ligature, leaving SHADDA,FATHA next to eachother.
				 *   However, it would be wrong to ligate that SHADDA,FATHA sequence.
				 *   There is an exception to this: If a ligature tries ligating with marks that
				 *   belong to it itself, go ahead, assuming that the font designer knows what
				 *   they are doing (otherwise it can break Indic stuff when a matra wants to
				 *   ligate with a conjunct...)
				 */

				/*
				 * - If a ligature is formed of components that some of which are also ligatures
				 *   themselves, and those ligature components had marks attached to *their*
				 *   components, we have to attach the marks to the new ligature component
				 *   positions!  Now *that*'s tricky!  And these marks may be following the
				 *   last component of the whole sequence, so we should loop forward looking
				 *   for them and update them.
				 *
				 *   Eg. the sequence is LAM,LAM,SHADDA,FATHA,HEH, and the font first forms a
				 *   'calt' ligature of LAM,HEH, leaving the SHADDA and FATHA with a ligature
				 *   id and component == 1.  Now, during 'liga', the LAM and the LAM-HEH ligature
				 *   form a LAM-LAM-HEH ligature.  We need to reassign the SHADDA and FATHA to
				 *   the new ligature with a component value of 2.
				 *
				 *   This in fact happened to a font...  See:
				 *   https://bugzilla.gnome.org/show_bug.cgi?id=437633
				 */

				$currComp = 0;
				for ($i = 0; $i < count($GlyphPos); $i++) {
					if ($i > 0 && isset($this->assocLigs[$GlyphPos[$i]])) { // One of the other components is already a ligature
						$nc = $this->assocLigs[$GlyphPos[$i]];
					} else {
						$nc = 1;
					}
					// While next char to right is a mark (but not the next matched glyph)
					// ?? + also include a Mark Ligature here
					$ic = 1;
					while ((($i == count($GlyphPos) - 1) || (isset($GlyphPos[$i + 1]) && ($GlyphPos[$i] + $ic) < $GlyphPos[$i + 1])) && isset($this->OTLdata[($GlyphPos[$i] + $ic)]) && strpos($this->GlyphClassMarks, $this->OTLdata[($GlyphPos[$i] + $ic)]['hex']) !== false) {
						$newComp = $currComp;
						if (isset($this->assocMarks[$GlyphPos[$i] + $ic])) { // One of the inbetween Marks is already associated with a Lig
							// OK as long as it is associated with the current Lig
							//      if ($this->assocMarks[($GlyphPos[$i]+$ic)]['ligPos'] != ($GlyphPos[$i]+$ic)) { die("Problem #1"); }
							$newComp += $this->assocMarks[($GlyphPos[$i] + $ic)]['compID'];
						}
						$this->assocMarks[($GlyphPos[$i] + $ic)] = array('compID' => $newComp, 'ligPos' => $pos);
						$ic++;
					}
					$currComp += $nc;
				}
				$lastPos = $GlyphPos[(count($GlyphPos) - 1)] + $ic - 1;
				$this->assocLigs[$pos] = $currComp; // Number of components in new Ligature
			}

			// Now remove the unwanted glyphs and associated metadata
			$newOTLdata[0] = array();

			// Get types of new inserted chars - or replicate type of char being replaced
			//  $bt = UCDN::get_bidi_class($substitute);
			//  if (!$bt) {
			$bt = $this->OTLdata[$pos]['bidi_type'];
			//  }

			if (strpos($this->GlyphClassMarks, $this->unicode_hex($substitute)) !== false) {
				$gp = 'M';
			} else if ($substitute == 32) {
				$gp = 'S';
			} else {
				$gp = 'C';
			}

			// Need to update details of new glyphs inserted
			$newOTLdata[0]['general_category'] = $this->OTLdata[$pos]['general_category'];

			$newOTLdata[0]['bidi_type'] = $bt;
			$newOTLdata[0]['group'] = $gp;

			// KASHIDA: If forming a ligature when the last component was identified as a kashida point (final form)
			// If previous/first component of ligature is a medial form, then keep this as a kashida point
			// TEST (Arabic Typesetting) &#x64a;&#x64e;&#x646;&#x62a;&#x64f;&#x645;
			$ka = 0;
			if (isset($this->OTLdata[$GlyphPos[(count($GlyphPos) - 1)]]['GPOSinfo']['kashida'])) {
				$ka = $this->OTLdata[$GlyphPos[(count($GlyphPos) - 1)]]['GPOSinfo']['kashida'];
			}
			if ($ka == 1 && isset($this->OTLdata[$pos]['form']) && $this->OTLdata[$pos]['form'] == 3) {
				$newOTLdata[0]['GPOSinfo']['kashida'] = $ka;
			}

			$newOTLdata[0]['uni'] = $substitute;
			$newOTLdata[0]['hex'] = $this->unicode_hex($substitute);

			if ($this->shaper == 'I' || $this->shaper == 'K' || $this->shaper == 'S') {
				$newOTLdata[0]['indic_category'] = $this->OTLdata[$pos]['indic_category'];
				$newOTLdata[0]['indic_position'] = $this->OTLdata[$pos]['indic_position'];
			} else if ($this->shaper == 'M') {
				$newOTLdata[0]['myanmar_category'] = $this->OTLdata[$pos]['myanmar_category'];
				$newOTLdata[0]['myanmar_position'] = $this->OTLdata[$pos]['myanmar_position'];
			}
			if (isset($this->OTLdata[$pos]['mask'])) {
				$newOTLdata[0]['mask'] = $this->OTLdata[$pos]['mask'];
			}
			if (isset($this->OTLdata[$pos]['syllable'])) {
				$newOTLdata[0]['syllable'] = $this->OTLdata[$pos]['syllable'];
			}

			$newOTLdata[0]['is_ligature'] = true;


			array_splice($this->OTLdata, $pos, 1, $newOTLdata);

			// GlyphPos contains array of arr_pos to set null - not necessarily contiguous
			// +- Remove any assocMarks or assocLigs from the main components (the ones that are deleted)
			for ($i = count($GlyphPos) - 1; $i > 0; $i--) {
				$gpos = $GlyphPos[$i];
				array_splice($this->OTLdata, $gpos, 1);
				unset($this->assocLigs[$gpos]);
				unset($this->assocMarks[$gpos]);
			}
			//  $this->assocLigs = array(); // Ligatures[$posarr lpos] => nc
			//  $this->assocMarks = array();    // assocMarks[$posarr mpos] => array(compID, ligPos)
			// Update position of pre-existing Ligatures and associated Marks
			// Start after first GlyphPos
			// count($GlyphPos)-1  is the number of glyphs removed from string
			for ($p = ($GlyphPos[0] + 1); $p < (count($this->OTLdata) + count($GlyphPos) - 1); $p++) {
				$nrem = 0; // Number of Glyphs removed at this point in the string
				for ($i = 0; $i < count($GlyphPos); $i++) {
					if ($i > 0 && $p > $GlyphPos[$i]) {
						$nrem++;
					}
				}
				if (isset($this->assocLigs[$p])) {
					$tmp = $this->assocLigs[$p];
					unset($this->assocLigs[$p]);
					$this->assocLigs[($p - $nrem)] = $tmp;
				}
				if (isset($this->assocMarks[$p])) {
					$tmp = $this->assocMarks[$p];
					unset($this->assocMarks[$p]);
					if ($tmp['ligPos'] > $GlyphPos[0]) {
						$tmp['ligPos'] -= $nrem;
					}
					$this->assocMarks[($p - $nrem)] = $tmp;
				}
			}
			return 1;
		} else {
			return 0;
		}
	}

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////
//////////       ARABIC        /////////////////////////////////
////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

	function arabic_initialise()
	{
		// cf. http://unicode.org/Public/UNIDATA/ArabicShaping.txt
		// http://unicode.org/Public/UNIDATA/extracted/DerivedJoiningType.txt
		// JOIN TO FOLLOWING LETTER IN LOGICAL ORDER (i.e. AS INITIAL/MEDIAL FORM) = Unicode Left-Joining (+ Dual-Joining + Join_Causing 00640)
		$this->arabLeftJoining = array(
			0x0620 => 1, 0x0626 => 1, 0x0628 => 1, 0x062A => 1, 0x062B => 1, 0x062C => 1, 0x062D => 1, 0x062E => 1,
			0x0633 => 1, 0x0634 => 1, 0x0635 => 1, 0x0636 => 1, 0x0637 => 1, 0x0638 => 1, 0x0639 => 1, 0x063A => 1,
			0x063B => 1, 0x063C => 1, 0x063D => 1, 0x063E => 1, 0x063F => 1, 0x0640 => 1, 0x0641 => 1, 0x0642 => 1,
			0x0643 => 1, 0x0644 => 1, 0x0645 => 1, 0x0646 => 1, 0x0647 => 1, 0x0649 => 1, 0x064A => 1, 0x066E => 1,
			0x066F => 1, 0x0678 => 1, 0x0679 => 1, 0x067A => 1, 0x067B => 1, 0x067C => 1, 0x067D => 1, 0x067E => 1,
			0x067F => 1, 0x0680 => 1, 0x0681 => 1, 0x0682 => 1, 0x0683 => 1, 0x0684 => 1, 0x0685 => 1, 0x0686 => 1,
			0x0687 => 1, 0x069A => 1, 0x069B => 1, 0x069C => 1, 0x069D => 1, 0x069E => 1, 0x069F => 1, 0x06A0 => 1,
			0x06A1 => 1, 0x06A2 => 1, 0x06A3 => 1, 0x06A4 => 1, 0x06A5 => 1, 0x06A6 => 1, 0x06A7 => 1, 0x06A8 => 1,
			0x06A9 => 1, 0x06AA => 1, 0x06AB => 1, 0x06AC => 1, 0x06AD => 1, 0x06AE => 1, 0x06AF => 1, 0x06B0 => 1,
			0x06B1 => 1, 0x06B2 => 1, 0x06B3 => 1, 0x06B4 => 1, 0x06B5 => 1, 0x06B6 => 1, 0x06B7 => 1, 0x06B8 => 1,
			0x06B9 => 1, 0x06BA => 1, 0x06BB => 1, 0x06BC => 1, 0x06BD => 1, 0x06BE => 1, 0x06BF => 1, 0x06C1 => 1,
			0x06C2 => 1, 0x06CC => 1, 0x06CE => 1, 0x06D0 => 1, 0x06D1 => 1, 0x06FA => 1, 0x06FB => 1, 0x06FC => 1,
			0x06FF => 1,
			/* Arabic Supplement */
			0x0750 => 1, 0x0751 => 1, 0x0752 => 1, 0x0753 => 1, 0x0754 => 1, 0x0755 => 1, 0x0756 => 1, 0x0757 => 1,
			0x0758 => 1, 0x075C => 1, 0x075D => 1, 0x075E => 1, 0x075F => 1, 0x0760 => 1, 0x0761 => 1, 0x0762 => 1,
			0x0763 => 1, 0x0764 => 1, 0x0765 => 1, 0x0766 => 1, 0x0767 => 1, 0x0768 => 1, 0x0769 => 1, 0x076A => 1,
			0x076D => 1, 0x076E => 1, 0x076F => 1, 0x0770 => 1, 0x0772 => 1, 0x0775 => 1, 0x0776 => 1, 0x0777 => 1,
			0x077A => 1, 0x077B => 1, 0x077C => 1, 0x077D => 1, 0x077E => 1, 0x077F => 1,
			/* Extended Arabic */
			0x08A0 => 1, 0x08A2 => 1, 0x08A3 => 1, 0x08A4 => 1, 0x08A5 => 1, 0x08A6 => 1, 0x08A7 => 1, 0x08A8 => 1,
			0x08A9 => 1,
			/* 'syrc' Syriac */
			0x0712 => 1, 0x0713 => 1, 0x0714 => 1, 0x071A => 1, 0x071B => 1, 0x071C => 1, 0x071D => 1, 0x071F => 1,
			0x0720 => 1, 0x0721 => 1, 0x0722 => 1, 0x0723 => 1, 0x0724 => 1, 0x0725 => 1, 0x0726 => 1, 0x0727 => 1,
			0x0729 => 1, 0x072B => 1, 0x072D => 1, 0x072E => 1, 0x074E => 1, 0x074F => 1,
			/* N'Ko */
			0x07CA => 1, 0x07CB => 1, 0x07CC => 1, 0x07CD => 1, 0x07CE => 1, 0x07CF => 1, 0x07D0 => 1, 0x07D1 => 1,
			0x07D2 => 1, 0x07D3 => 1, 0x07D4 => 1, 0x07D5 => 1, 0x07D6 => 1, 0x07D7 => 1, 0x07D8 => 1, 0x07D9 => 1,
			0x07DA => 1, 0x07DB => 1, 0x07DC => 1, 0x07DD => 1, 0x07DE => 1, 0x07DF => 1, 0x07E0 => 1, 0x07E1 => 1,
			0x07E2 => 1, 0x07E3 => 1, 0x07E4 => 1, 0x07E5 => 1, 0x07E6 => 1, 0x07E7 => 1, 0x07E8 => 1, 0x07E9 => 1,
			0x07EA => 1, 0x07FA => 1,
			/* Mandaic */
			0x0841 => 1, 0x0842 => 1, 0x0843 => 1, 0x0844 => 1, 0x0845 => 1, 0x0847 => 1, 0x0848 => 1, 0x084A => 1,
			0x084B => 1, 0x084C => 1, 0x084D => 1, 0x084E => 1, 0x0850 => 1, 0x0851 => 1, 0x0852 => 1, 0x0853 => 1,
			0x0855 => 1,
			/* ZWJ U+200D */
			0x0200D => 1);

		/* JOIN TO PREVIOUS LETTER IN LOGICAL ORDER (i.e. AS FINAL/MEDIAL FORM) = Unicode Right-Joining (+ Dual-Joining + Join_Causing) */
		$this->arabRightJoining = array(
			0x0620 => 1, 0x0622 => 1, 0x0623 => 1, 0x0624 => 1, 0x0625 => 1, 0x0626 => 1, 0x0627 => 1, 0x0628 => 1,
			0x0629 => 1, 0x062A => 1, 0x062B => 1, 0x062C => 1, 0x062D => 1, 0x062E => 1, 0x062F => 1, 0x0630 => 1,
			0x0631 => 1, 0x0632 => 1, 0x0633 => 1, 0x0634 => 1, 0x0635 => 1, 0x0636 => 1, 0x0637 => 1, 0x0638 => 1,
			0x0639 => 1, 0x063A => 1, 0x063B => 1, 0x063C => 1, 0x063D => 1, 0x063E => 1, 0x063F => 1, 0x0640 => 1,
			0x0641 => 1, 0x0642 => 1, 0x0643 => 1, 0x0644 => 1, 0x0645 => 1, 0x0646 => 1, 0x0647 => 1, 0x0648 => 1,
			0x0649 => 1, 0x064A => 1, 0x066E => 1, 0x066F => 1, 0x0671 => 1, 0x0672 => 1, 0x0673 => 1, 0x0675 => 1,
			0x0676 => 1, 0x0677 => 1, 0x0678 => 1, 0x0679 => 1, 0x067A => 1, 0x067B => 1, 0x067C => 1, 0x067D => 1,
			0x067E => 1, 0x067F => 1, 0x0680 => 1, 0x0681 => 1, 0x0682 => 1, 0x0683 => 1, 0x0684 => 1, 0x0685 => 1,
			0x0686 => 1, 0x0687 => 1, 0x0688 => 1, 0x0689 => 1, 0x068A => 1, 0x068B => 1, 0x068C => 1, 0x068D => 1,
			0x068E => 1, 0x068F => 1, 0x0690 => 1, 0x0691 => 1, 0x0692 => 1, 0x0693 => 1, 0x0694 => 1, 0x0695 => 1,
			0x0696 => 1, 0x0697 => 1, 0x0698 => 1, 0x0699 => 1, 0x069A => 1, 0x069B => 1, 0x069C => 1, 0x069D => 1,
			0x069E => 1, 0x069F => 1, 0x06A0 => 1, 0x06A1 => 1, 0x06A2 => 1, 0x06A3 => 1, 0x06A4 => 1, 0x06A5 => 1,
			0x06A6 => 1, 0x06A7 => 1, 0x06A8 => 1, 0x06A9 => 1, 0x06AA => 1, 0x06AB => 1, 0x06AC => 1, 0x06AD => 1,
			0x06AE => 1, 0x06AF => 1, 0x06B0 => 1, 0x06B1 => 1, 0x06B2 => 1, 0x06B3 => 1, 0x06B4 => 1, 0x06B5 => 1,
			0x06B6 => 1, 0x06B7 => 1, 0x06B8 => 1, 0x06B9 => 1, 0x06BA => 1, 0x06BB => 1, 0x06BC => 1, 0x06BD => 1,
			0x06BE => 1, 0x06BF => 1, 0x06C0 => 1, 0x06C1 => 1, 0x06C2 => 1, 0x06C3 => 1, 0x06C4 => 1, 0x06C5 => 1,
			0x06C6 => 1, 0x06C7 => 1, 0x06C8 => 1, 0x06C9 => 1, 0x06CA => 1, 0x06CB => 1, 0x06CC => 1, 0x06CD => 1,
			0x06CE => 1, 0x06CF => 1, 0x06D0 => 1, 0x06D1 => 1, 0x06D2 => 1, 0x06D3 => 1, 0x06D5 => 1, 0x06EE => 1,
			0x06EF => 1, 0x06FA => 1, 0x06FB => 1, 0x06FC => 1, 0x06FF => 1,
			/* Arabic Supplement */
			0x0750 => 1, 0x0751 => 1, 0x0752 => 1, 0x0753 => 1, 0x0754 => 1, 0x0755 => 1, 0x0756 => 1, 0x0757 => 1,
			0x0758 => 1, 0x0759 => 1, 0x075A => 1, 0x075B => 1, 0x075C => 1, 0x075D => 1, 0x075E => 1, 0x075F => 1,
			0x0760 => 1, 0x0761 => 1, 0x0762 => 1, 0x0763 => 1, 0x0764 => 1, 0x0765 => 1, 0x0766 => 1, 0x0767 => 1,
			0x0768 => 1, 0x0769 => 1, 0x076A => 1, 0x076B => 1, 0x076C => 1, 0x076D => 1, 0x076E => 1, 0x076F => 1,
			0x0770 => 1, 0x0771 => 1, 0x0772 => 1, 0x0773 => 1, 0x0774 => 1, 0x0775 => 1, 0x0776 => 1, 0x0777 => 1,
			0x0778 => 1, 0x0779 => 1, 0x077A => 1, 0x077B => 1, 0x077C => 1, 0x077D => 1, 0x077E => 1, 0x077F => 1,
			/* Extended Arabic */
			0x08A0 => 1, 0x08A2 => 1, 0x08A3 => 1, 0x08A4 => 1, 0x08A5 => 1, 0x08A6 => 1, 0x08A7 => 1, 0x08A8 => 1,
			0x08A9 => 1, 0x08AA => 1, 0x08AB => 1, 0x08AC => 1,
			/* 'syrc' Syriac */
			0x0710 => 1, 0x0712 => 1, 0x0713 => 1, 0x0714 => 1, 0x0715 => 1, 0x0716 => 1, 0x0717 => 1, 0x0718 => 1,
			0x0719 => 1, 0x071A => 1, 0x071B => 1, 0x071C => 1, 0x071D => 1, 0x071E => 1, 0x071F => 1, 0x0720 => 1,
			0x0721 => 1, 0x0722 => 1, 0x0723 => 1, 0x0724 => 1, 0x0725 => 1, 0x0726 => 1, 0x0727 => 1, 0x0728 => 1,
			0x0729 => 1, 0x072A => 1, 0x072B => 1, 0x072C => 1, 0x072D => 1, 0x072E => 1, 0x072F => 1, 0x074D => 1,
			0x074E => 1, 0x074F,
			/* N'Ko */
			0x07CA => 1, 0x07CB => 1, 0x07CC => 1, 0x07CD => 1, 0x07CE => 1, 0x07CF => 1, 0x07D0 => 1, 0x07D1 => 1,
			0x07D2 => 1, 0x07D3 => 1, 0x07D4 => 1, 0x07D5 => 1, 0x07D6 => 1, 0x07D7 => 1, 0x07D8 => 1, 0x07D9 => 1,
			0x07DA => 1, 0x07DB => 1, 0x07DC => 1, 0x07DD => 1, 0x07DE => 1, 0x07DF => 1, 0x07E0 => 1, 0x07E1 => 1,
			0x07E2 => 1, 0x07E3 => 1, 0x07E4 => 1, 0x07E5 => 1, 0x07E6 => 1, 0x07E7 => 1, 0x07E8 => 1, 0x07E9 => 1,
			0x07EA => 1, 0x07FA => 1,
			/* Mandaic */
			0x0841 => 1, 0x0842 => 1, 0x0843 => 1, 0x0844 => 1, 0x0845 => 1, 0x0847 => 1, 0x0848 => 1, 0x084A => 1,
			0x084B => 1, 0x084C => 1, 0x084D => 1, 0x084E => 1, 0x0850 => 1, 0x0851 => 1, 0x0852 => 1, 0x0853 => 1,
			0x0855 => 1,
			0x0840 => 1, 0x0846 => 1, 0x0849 => 1, 0x084F => 1, 0x0854 => 1, /* Right joining */
			/* ZWJ U+200D */
			0x0200D => 1);


		/* VOWELS = TRANSPARENT-JOINING = Unicode Transparent-Joining type (not just vowels) */
		$this->arabTransparent = array(
			0x0610 => 1, 0x0611 => 1, 0x0612 => 1, 0x0613 => 1, 0x0614 => 1, 0x0615 => 1, 0x0616 => 1, 0x0617 => 1,
			0x0618 => 1, 0x0619 => 1, 0x061A => 1, 0x064B => 1, 0x064C => 1, 0x064D => 1, 0x064E => 1, 0x064F => 1,
			0x0650 => 1, 0x0651 => 1, 0x0652 => 1, 0x0653 => 1, 0x0654 => 1, 0x0655 => 1, 0x0656 => 1, 0x0657 => 1,
			0x0658 => 1, 0x0659 => 1, 0x065A => 1, 0x065B => 1, 0x065C => 1, 0x065D => 1, 0x065E => 1, 0x065F => 1,
			0x0670 => 1, 0x06D6 => 1, 0x06D7 => 1, 0x06D8 => 1, 0x06D9 => 1, 0x06DA => 1, 0x06DB => 1, 0x06DC => 1,
			0x06DF => 1, 0x06E0 => 1, 0x06E1 => 1, 0x06E2 => 1, 0x06E3 => 1, 0x06E4 => 1, 0x06E7 => 1, 0x06E8 => 1,
			0x06EA => 1, 0x06EB => 1, 0x06EC => 1, 0x06ED => 1,
			/* Extended Arabic */
			0x08E4 => 1, 0x08E5 => 1, 0x08E6 => 1, 0x08E7 => 1, 0x08E8 => 1, 0x08E9 => 1, 0x08EA => 1, 0x08EB => 1,
			0x08EC => 1, 0x08ED => 1, 0x08EE => 1, 0x08EF => 1, 0x08F0 => 1, 0x08F1 => 1, 0x08F2 => 1, 0x08F3 => 1,
			0x08F4 => 1, 0x08F5 => 1, 0x08F6 => 1, 0x08F7 => 1, 0x08F8 => 1, 0x08F9 => 1, 0x08FA => 1, 0x08FB => 1,
			0x08FC => 1, 0x08FD => 1, 0x08FE => 1,
			/* Arabic ligatures in presentation form (converted in 'ccmp' in e.g. Arial and Times ? need to add others in this range) */
			0xFC5E => 1, 0xFC5F => 1, 0xFC60 => 1, 0xFC61 => 1, 0xFC62 => 1,
			/*  'syrc' Syriac */
			0x070F => 1, 0x0711 => 1, 0x0730 => 1, 0x0731 => 1, 0x0732 => 1, 0x0733 => 1, 0x0734 => 1, 0x0735 => 1,
			0x0736 => 1, 0x0737 => 1, 0x0738 => 1, 0x0739 => 1, 0x073A => 1, 0x073B => 1, 0x073C => 1, 0x073D => 1,
			0x073E => 1, 0x073F => 1, 0x0740 => 1, 0x0741 => 1, 0x0742 => 1, 0x0743 => 1, 0x0744 => 1, 0x0745 => 1,
			0x0746 => 1, 0x0747 => 1, 0x0748 => 1, 0x0749 => 1, 0x074A => 1,
			/* N'Ko */
			0x07EB => 1, 0x07EC => 1, 0x07ED => 1, 0x07EE => 1, 0x07EF => 1, 0x07F0 => 1, 0x07F1 => 1, 0x07F2 => 1,
			0x07F3 => 1,
			/* Mandaic */
			0x0859 => 1, 0x085A => 1, 0x085B => 1,
		);
	}

	function arabic_shaper($usetags, $scriptTag)
	{
		$chars = array();
		for ($i = 0; $i < count($this->OTLdata); $i++) {
			$chars[] = $this->OTLdata[$i]['hex'];
		}
		$crntChar = null;
		$prevChar = null;
		$nextChar = null;
		$output = array();
		$max = count($chars);
		for ($i = $max - 1; $i >= 0; $i--) {
			$crntChar = $chars[$i];
			if ($i > 0) {
				$prevChar = hexdec($chars[$i - 1]);
			} else {
				$prevChar = NULL;
			}
			if ($prevChar && isset($this->arabTransparentJoin[$prevChar]) && isset($chars[$i - 2])) {
				$prevChar = hexdec($chars[$i - 2]);
				if ($prevChar && isset($this->arabTransparentJoin[$prevChar]) && isset($chars[$i - 3])) {
					$prevChar = hexdec($chars[$i - 3]);
					if ($prevChar && isset($this->arabTransparentJoin[$prevChar]) && isset($chars[$i - 4])) {
						$prevChar = hexdec($chars[$i - 4]);
					}
				}
			}
			if ($crntChar && isset($this->arabTransparentJoin[hexdec($crntChar)])) {
				// If next_char = RightJoining && prev_char = LeftJoining:
				if (isset($chars[$i + 1]) && $chars[$i + 1] && isset($this->arabRightJoining[hexdec($chars[$i + 1])]) && $prevChar && isset($this->arabLeftJoining[$prevChar])) {
					$output[] = $this->get_arab_glyphs($crntChar, 1, $chars, $i, $scriptTag, $usetags); // <final> form
				} else {
					$output[] = $this->get_arab_glyphs($crntChar, 0, $chars, $i, $scriptTag, $usetags);  // <isolated> form
				}
				continue;
			}
			if (hexdec($crntChar) < 128) {
				$output[] = array($crntChar, 0);
				$nextChar = $crntChar;
				continue;
			}
			// 0=ISOLATED FORM :: 1=FINAL :: 2=INITIAL :: 3=MEDIAL
			$form = 0;
			if ($prevChar && isset($this->arabLeftJoining[$prevChar])) {
				$form++;
			}
			if ($nextChar && isset($this->arabRightJoining[hexdec($nextChar)])) {
				$form += 2;
			}
			$output[] = $this->get_arab_glyphs($crntChar, $form, $chars, $i, $scriptTag, $usetags);
			$nextChar = $crntChar;
		}
		$ra = array_reverse($output);
		for ($i = 0; $i < count($this->OTLdata); $i++) {
			$this->OTLdata[$i]['uni'] = hexdec($ra[$i][0]);
			$this->OTLdata[$i]['hex'] = $ra[$i][0];
			$this->OTLdata[$i]['form'] = $ra[$i][1]; // Actaul form substituted 0=ISOLATED FORM :: 1=FINAL :: 2=INITIAL :: 3=MEDIAL
		}
	}

	function get_arab_glyphs($char, $type, &$chars, $i, $scriptTag, $usetags)
	{

		// Optional Feature settings    // doesn't control Syriac at present
		if (($type === 0 && strpos($usetags, 'isol') === false) || ($type === 1 && strpos($usetags, 'fina') === false) || ($type === 2 && strpos($usetags, 'init') === false) || ($type === 3 && strpos($usetags, 'medi') === false)) {
			return array($char, 0);
		}

		// 0=ISOLATED FORM :: 1=FINAL :: 2=INITIAL :: 3=MEDIAL (:: 4=MED2 :: 5=FIN2 :: 6=FIN3)
		$retk = -1;
		// Alaph 00710 in Syriac
		if ($scriptTag == 'syrc' && $char == '00710') {
			// if there is a preceding (base?) character *** should search back to previous base - ignoring vowels and change $n
			// set $n as the position of the last base; for now we'll just do this:
			$n = $i - 1;
			// if the preceding (base) character cannot be joined to
			// not in $this->arabLeftJoining i.e. not a char which can join to the next one
			if (isset($chars[$n]) && isset($this->arabLeftJoining[hexdec($chars[$n])])) {
				// if in the middle of Syriac words
				if (isset($chars[$i + 1]) && preg_match('/[\x{0700}-\x{0745}]/u', code2utf(hexdec($chars[$n]))) && preg_match('/[\x{0700}-\x{0745}]/u', code2utf(hexdec($chars[$i + 1]))) && isset($this->arabGlyphs[$char][4])) {
					$retk = 4;
				}
				// if at the end of Syriac words
				else if (!isset($chars[$i + 1]) || !preg_match('/[\x{0700}-\x{0745}]/u', code2utf(hexdec($chars[$i + 1])))) {
					// if preceding base character IS (00715|00716|0072A)
					if (strpos('0715|0716|072A', $chars[$n]) !== false && isset($this->arabGlyphs[$char][6])) {
						$retk = 6;
					}

					// else if preceding base character is NOT (00715|00716|0072A)
					else if (isset($this->arabGlyphs[$char][5])) {
						$retk = 5;
					}
				}
			}
			if ($retk != -1) {
				return array($this->arabGlyphs[$char][$retk], $retk);
			} else {
				return array($char, 0);
			}
		}

		if (($type > 0 || $type === 0) && isset($this->arabGlyphs[$char][$type])) {
			$retk = $type;
		} else if ($type == 3 && isset($this->arabGlyphs[$char][1])) { // if <medial> not defined, but <final>, return <final>
			$retk = 1;
		} else if ($type == 2 && isset($this->arabGlyphs[$char][0])) { // if <initial> not defined, but <isolated>, return <isolated>
			$retk = 0;
		}
		if ($retk != -1) {
			$match = true;
			// If GSUB includes a Backtrack or Lookahead condition (e.g. font ArabicTypesetting)
			if (isset($this->arabGlyphs[$char]['prel'][$retk]) && $this->arabGlyphs[$char]['prel'][$retk]) {
				$ig = 1;
				foreach ($this->arabGlyphs[$char]['prel'][$retk] AS $k => $v) { // $k starts 0, 1...
					if (!isset($chars[$i - $ig - $k])) {
						$match = false;
					} else if (strpos($v, $chars[$i - $ig - $k]) === false) {
						while (strpos($this->arabGlyphs[$char]['ignore'][$retk], $chars[$i - $ig - $k]) !== false) {  // ignore
							$ig++;
						}
						if (!isset($chars[$i - $ig - $k])) {
							$match = false;
						} else if (strpos($v, $chars[$i - $ig - $k]) === false) {
							$match = false;
						}
					}
				}
			}
			if (isset($this->arabGlyphs[$char]['postl'][$retk]) && $this->arabGlyphs[$char]['postl'][$retk]) {
				$ig = 1;
				foreach ($this->arabGlyphs[$char]['postl'][$retk] AS $k => $v) { // $k starts 0, 1...
					if (!isset($chars[$i + $ig + $k])) {
						$match = false;
					} else if (strpos($v, $chars[$i + $ig + $k]) === false) {
						while (strpos($this->arabGlyphs[$char]['ignore'][$retk], $chars[$i + $ig + $k]) !== false) {  // ignore
							$ig++;
						}
						if (!isset($chars[$i + $ig + $k])) {
							$match = false;
						} else if (strpos($v, $chars[$i + $ig + $k]) === false) {
							$match = false;
						}
					}
				}
			}
			if ($match) {
				return array($this->arabGlyphs[$char][$retk], $retk);
			} else {
				return array($char, 0);
			}
		} else {
			return array($char, 0);
		}
	}

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////
/////////////////       LINE BREAKING    ///////////////////////
////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////
/////////////       TIBETAN LINE BREAKING    ///////////////////
////////////////////////////////////////////////////////////////
// Sets $this->OTLdata[$i]['wordend']=true at possible end of word boundaries
	function TibetanlineBreaking()
	{
		for ($ptr = 0; $ptr < count($this->OTLdata); $ptr++) {
			// Break opportunities at U+0F0B Tsheg or U=0F0D
			if (isset($this->OTLdata[$ptr]['uni']) && ($this->OTLdata[$ptr]['uni'] == 0x0F0B || $this->OTLdata[$ptr]['uni'] == 0x0F0D)) {
				if (isset($this->OTLdata[$ptr + 1]['uni']) && ($this->OTLdata[$ptr + 1]['uni'] == 0x0F0D || $this->OTLdata[$ptr + 1]['uni'] == 0xF0E)) {
					continue;
				}
				// Set end of word marker in OTLdata at matchpos
				$this->OTLdata[$ptr]['wordend'] = true;
			}
		}
	}

////////////////////////////////////////////////////////////////
//////////       SOUTH EAST ASIAN LINE BREAKING    /////////////
////////////////////////////////////////////////////////////////
// South East Asian Linebreaking (Thai, Khmer and Lao) using dictionary of words
// Sets $this->OTLdata[$i]['wordend']=true at possible end of word boundaries
	function SEAlineBreaking()
	{
		// Load Line-breaking dictionary
		if (!isset($this->lbdicts[$this->shaper]) && file_exists(_MPDF_PATH . 'includes/linebrdict' . $this->shaper . '.dat')) {
			$this->lbdicts[$this->shaper] = file_get_contents(_MPDF_PATH . 'includes/linebrdict' . $this->shaper . '.dat');
		}

		$dict = &$this->lbdicts[$this->shaper];

		// Find all word boundaries and mark end of word $this->OTLdata[$i]['wordend']=true on last character
		// If Thai, allow for possible suffixes (not in Lao or Khmer)
		// repeater/ellision characters
		// (0x0E2F);        // Ellision character THAI_PAIYANNOI 0x0E2F  UTF-8 0xE0 0xB8 0xAF
		// (0x0E46);        // Repeat character THAI_MAIYAMOK 0x0E46   UTF-8 0xE0 0xB9 0x86
		// (0x0EC6);        // Repeat character LAO   UTF-8 0xE0 0xBB 0x86

		$rollover = array();
		$ptr = 0;
		while ($ptr < count($this->OTLdata) - 3) {
			if (count($rollover)) {
				$matches = $rollover;
				$rollover = array();
			} else {
				$matches = $this->checkwordmatch($dict, $ptr);
			}
			if (count($matches) == 1) {
				$matchpos = $matches[0];
				// Check for repeaters - if so $matchpos++
				if (isset($this->OTLdata[$matchpos + 1]['uni']) && ($this->OTLdata[$matchpos + 1]['uni'] == 0x0E2F || $this->OTLdata[$matchpos + 1]['uni'] == 0x0E46 || $this->OTLdata[$matchpos + 1]['uni'] == 0x0EC6)) {
					$matchpos++;
				}
				// Set end of word marker in OTLdata at matchpos
				$this->OTLdata[$matchpos]['wordend'] = true;
				$ptr = $matchpos + 1;
			} else if (empty($matches)) {
				$ptr++;
				// Move past any ASCII characters
				while (isset($this->OTLdata[$ptr]['uni']) && ($this->OTLdata[$ptr]['uni'] >> 8) == 0) {
					$ptr++;
				}
			} else { // Multiple matches
				$secondmatch = false;
				for ($m = count($matches) - 1; $m >= 0; $m--) {
					//for ($m=0;$m<count($matches);$m++) {
					$firstmatch = $matches[$m];
					$matches2 = $this->checkwordmatch($dict, $firstmatch + 1);
					if (count($matches2)) {
						// Set end of word marker in OTLdata at matchpos
						$this->OTLdata[$firstmatch]['wordend'] = true;
						$ptr = $firstmatch + 1;
						$rollover = $matches2;
						$secondmatch = true;
						break;
					}
				}
				if (!$secondmatch) {
					// Set end of word marker in OTLdata at end of longest first match
					$this->OTLdata[$matches[count($matches) - 1]]['wordend'] = true;
					$ptr = $matches[count($matches) - 1] + 1;
					// Move past any ASCII characters
					while (isset($this->OTLdata[$ptr]['uni']) && ($this->OTLdata[$ptr]['uni'] >> 8) == 0) {
						$ptr++;
					}
				}
			}
		}
	}

	function checkwordmatch(&$dict, $ptr)
	{
		/*
		  define("_DICT_NODE_TYPE_SPLIT", 0x01);
		  define("_DICT_NODE_TYPE_LINEAR", 0x02);
		  define("_DICT_INTERMEDIATE_MATCH", 0x03);
		  define("_DICT_FINAL_MATCH", 0x04);

		  Node type: Split.
		  Divide at < 98 >= 98
		  Offset for >= 98 == 79    (long 4-byte unsigned)

		  Node type: Linear match.
		  Char = 97

		  Intermediate match

		  Final match
		 */

		$dictptr = 0;
		$ok = true;
		$matches = array();
		while ($ok) {
			$x = ord($dict{$dictptr});
			$c = $this->OTLdata[$ptr]['uni'] & 0xFF;
			if ($x == _DICT_INTERMEDIATE_MATCH) {
//echo "DICT_INTERMEDIATE_MATCH: ".dechex($c).'<br />';
				// Do not match if next character in text is a Mark
				if (isset($this->OTLdata[$ptr]['uni']) && strpos($this->GlyphClassMarks, $this->OTLdata[$ptr]['hex']) === false) {
					$matches[] = $ptr - 1;
				}
				$dictptr++;
			} else if ($x == _DICT_FINAL_MATCH) {
//echo "DICT_FINAL_MATCH: ".dechex($c).'<br />';
				// Do not match if next character in text is a Mark
				if (isset($this->OTLdata[$ptr]['uni']) && strpos($this->GlyphClassMarks, $this->OTLdata[$ptr]['hex']) === false) {
					$matches[] = $ptr - 1;
				}
				return $matches;
			} else if ($x == _DICT_NODE_TYPE_LINEAR) {
//echo "DICT_NODE_TYPE_LINEAR: ".dechex($c).'<br />';
				$dictptr++;
				$m = ord($dict{$dictptr});
				if ($c == $m) {
					$ptr++;
					if ($ptr > count($this->OTLdata) - 1) {
						$next = ord($dict{$dictptr + 1});
						if ($next == _DICT_INTERMEDIATE_MATCH || $next == _DICT_FINAL_MATCH) {
							// Do not match if next character in text is a Mark
							if (isset($this->OTLdata[$ptr]['uni']) && strpos($this->GlyphClassMarks, $this->OTLdata[$ptr]['hex']) === false) {
								$matches[] = $ptr - 1;
							}
						}
						return $matches;
					}
					$dictptr++;
					continue;
				} else {
//echo "DICT_NODE_TYPE_LINEAR NOT: ".dechex($c).'<br />';
					return $matches;
				}
			} else if ($x == _DICT_NODE_TYPE_SPLIT) {
//echo "DICT_NODE_TYPE_SPLIT ON ".dechex($d).": ".dechex($c).'<br />';
				$dictptr++;
				$d = ord($dict{$dictptr});
				if ($c < $d) {
					$dictptr += 5;
				} else {
					$dictptr++;
					// Unsigned long 32-bit offset
					$offset = (ord($dict{$dictptr}) * 16777216) + (ord($dict{$dictptr + 1}) << 16) + (ord($dict{$dictptr + 2}) << 8) + ord($dict{$dictptr + 3});
					$dictptr = $offset;
				}
			} else {
//echo "PROBLEM: ".($x).'<br />';
				$ok = false; // Something has gone wrong
			}
		}

		return $matches;
	}

////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////
//////////       GPOS    ///////////////////////////////////////
////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////

	function _applyGPOSrules($LookupList, $is_old_spec = false)
	{
		foreach ($LookupList AS $lu => $tag) {
			$Type = $this->GPOSLookups[$lu]['Type'];
			$Flag = $this->GPOSLookups[$lu]['Flag'];
			$MarkFilteringSet = '';
			if (isset($this->GPOSLookups[$lu]['MarkFilteringSet']))
				$MarkFilteringSet = $this->GPOSLookups[$lu]['MarkFilteringSet'];
			$ptr = 0;
			// Test each glyph sequentially
			while ($ptr < (count($this->OTLdata))) { // whilst there is another glyph ..0064
				$currGlyph = $this->OTLdata[$ptr]['hex'];
				$currGID = $this->OTLdata[$ptr]['uni'];
				$shift = 1;
				foreach ($this->GPOSLookups[$lu]['Subtables'] AS $c => $subtable_offset) {
					// NB Coverage only looks at glyphs for position 1 (esp. 7.3 and 8.3)
					if (isset($this->LuCoverage[$lu][$c][$currGID])) {
						// Get rules from font GPOS subtable
						if (isset($this->OTLdata[$ptr]['bidi_type'])) {  // No need to check bidi_type - just a check that it exists
							$shift = $this->_applyGPOSsubtable($lu, $c, $ptr, $currGlyph, $currGID, ($subtable_offset - $this->GPOS_offset + $this->GSUB_length), $Type, $Flag, $MarkFilteringSet, $this->LuCoverage[$lu][$c], $tag, 0, $is_old_spec);
							if ($shift) {
								break;
							}
						}
					}
				}
				if ($shift == 0) {
					$shift = 1;
				}
				$ptr += $shift;
			}
		}
	}

	//////////////////////////////////////////////////////////////////////////////////
	// GPOS Types
	// Lookup Type 1: Single Adjustment Positioning Subtable        Adjust position of a single glyph
	// Lookup Type 2: Pair Adjustment Positioning Subtable      Adjust position of a pair of glyphs
	// Lookup Type 3: Cursive Attachment Positioning Subtable       Attach cursive glyphs
	// Lookup Type 4: MarkToBase Attachment Positioning Subtable    Attach a combining mark to a base glyph
	// Lookup Type 5: MarkToLigature Attachment Positioning Subtable    Attach a combining mark to a ligature
	// Lookup Type 6: MarkToMark Attachment Positioning Subtable    Attach a combining mark to another mark
	// Lookup Type 7: Contextual Positioning Subtables          Position one or more glyphs in context
	// Lookup Type 8: Chaining Contextual Positioning Subtable      Position one or more glyphs in chained context
	// Lookup Type 9: Extension positioning
	//////////////////////////////////////////////////////////////////////////////////
	function _applyGPOSvaluerecord($basepos, $Value)
	{

		// If current glyph is a mark with a defined width, any XAdvance is considered to REPLACE the character Advanc