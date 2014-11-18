<?php
    /******************************************************************
     mod_password.php                                      Muze Ariadne
     ------------------------------------------------------------------
     Author: Muze (info@muze.nl)
     Date: 11 december 2002

     Copyright 2002 Muze

     This file is part of Ariadne.

     Ariadne is free software; you can redistribute it and/or modify
     it under the terms of the GNU General Public License as published
     by the Free Software Foundation; either version 2 of the License,
     or (at your option) any later version.

     Ariadne is distributed in the hope that it will be useful,
     but WITHOUT ANY WARRANTY; without even the implied warranty of
     MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
     GNU General Public License for more details.

     You should have received a copy of the GNU General Public License
     along with Ariadne; if not, write to the Free Software
     Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA
     02111-1307  USA

    -------------------------------------------------------------------

     Description:

 * Password generation based on :
 *
 * pw_phonemes.c --- generate secure passwords using phoneme rules
 *
 * Copyright (C) 2001,2002 by Theodore Ts'o
 *
 * This file may be distributed under the terms of the GNU Public
 * License.

   *********************************************************************/

class password {
	/*
	 * Flags for the pw_element
	 */
	const CONSONANT = 0x00001;
	const VOWEL     = 0x00002;
	const DIPTHONG  = 0x00004;
	const NOT_FIRST = 0x00008;
	/*
	 * Flags for the pwgen function
	 */
	const PW_ONE_NUMBER = 0x00001;
	const PW_ONE_CASE   = 0x00002;

	protected static $elements = array();
	protected static $NUM_ELEMENTS = 0;

	protected static function generateElements() {
		self::$elements = array(
			array( "a",	self::VOWEL ),
			array( "ae", self::VOWEL | self::DIPTHONG ),
			array( "ah",	self::VOWEL | self::DIPTHONG ),
			array( "ai", self::VOWEL | self::DIPTHONG ),
			array( "b",  self::CONSONANT ),
			array( "c",	self::CONSONANT ),
			array( "ch", self::CONSONANT | self::DIPTHONG ),
			array( "d",	self::CONSONANT ),
			array( "e",	self::VOWEL ),
			array( "ee", self::VOWEL | self::DIPTHONG ),
			array( "ei",	self::VOWEL | self::DIPTHONG ),
			array( "f",	self::CONSONANT ),
			array( "g",	self::CONSONANT ),
			array( "gh", self::CONSONANT | self::DIPTHONG | self::NOT_FIRST ),
			array( "h",	self::CONSONANT ),
			array( "i",	self::VOWEL ),
			array( "ie", self::VOWEL | self::DIPTHONG ),
			array( "j",	self::CONSONANT ),
			array( "k",	self::CONSONANT ),
			array( "l",	self::CONSONANT ),
			array( "m",	self::CONSONANT ),
			array( "n",	self::CONSONANT ),
			array( "ng",	self::CONSONANT | self::DIPTHONG | self::NOT_FIRST ),
			array( "o",	self::VOWEL ),
			array( "oh",	self::VOWEL | self::DIPTHONG ),
			array( "oo",	self::VOWEL | self::DIPTHONG),
			array( "p",	self::CONSONANT ),
			array( "ph",	self::CONSONANT | self::DIPTHONG ),
			array( "qu",	self::CONSONANT | self::DIPTHONG),
			array( "r",	self::CONSONANT ),
			array( "s",	self::CONSONANT ),
			array( "sh",	self::CONSONANT | self::DIPTHONG),
			array( "t",	self::CONSONANT ),
			array( "th",	self::CONSONANT | self::DIPTHONG),
			array( "u",	self::VOWEL ),
			array( "v",	self::CONSONANT ),
			array( "w",	self::CONSONANT ),
			array( "x",	self::CONSONANT ),
			array( "y",	self::CONSONANT ),
			array( "z",	self::CONSONANT )
		);
		self::$NUM_ELEMENTS = count( self::$elements );
	}

	public static function generate( $size=8, $pw_flags=3 ) {
		if ( self::$NUM_ELEMENTS == 0){
			self::generateElements();
		}

		do {
			$c = 1;
			$prev = 0;
			$should_be = 0;
			$first = 1;
			$result = "";

			$feature_flags = $pw_flags;

			$should_be = (rand(0,1) ? self::VOWEL : self::CONSONANT);

			while( $c < $size ) {
				$i = rand( 0, (self::$NUM_ELEMENTS - 1 ) );
				$str = self::$elements[$i][0];
				$len = strlen($str);
				$flags = self::$elements[$i][1];

				/* Filter on the basic type of the next element */
				if (($flags & $should_be) == 0) {
					continue;
				}
				/* Handle the self::NOT_FIRST flag */
				if ($first && ($flags & self::NOT_FIRST)){
					continue;
				}
				/* Don't allow self::VOWEL followed a Vowel/Dipthong pair */
				if ((prev & self::VOWEL) && ($flags & self::VOWEL) &&
					($flags & self::DIPTHONG)) {
						continue;
					}
				/* Don't allow us to overflow the buffer */
				if ($len > ( $size - $c ) ){
					continue;
				}
				/*
				 * OK, we found an element which matches our criteria,
				 * let's do it!
				 */

				$result = $result.$str;

				/* Handle self::PW_ONE_CASE */
				if ($feature_flags & self::PW_ONE_CASE) {
					if (($first || $flags & self::CONSONANT) && (rand(0,10) < 3)) {
						$result = substr_replace( $result, strtoupper(
							substr( $result, $c, 1 ) ), $c);
						$feature_flags &= ~self::PW_ONE_CASE;
					}
				}

				$c += $len;

				/* Time to stop? */
				if ($c >= $size){
					break;
				}

				/*
				 * Handle PW_ONE_NUMBER
				 */
				if ($feature_flags & self::PW_ONE_NUMBER) {
					if (!$first && (rand(0,10) < 3)) {
						$result = substr_replace( $result, rand(0,9), $c);
						$feature_flags &= ~PW_ONE_NUMBER;
						$first = 1;
						$prev = 0;
						$should_be = rand(0,1) ?
							self::VOWEL : self::CONSONANT;
						continue;
					}
				}

				/*
				 * OK, figure out what the next element should be
				 */
				if ($should_be == self::CONSONANT) {
					$should_be = self::VOWEL;
				} else { /* $should_be == self::VOWEL */
					if ( ($prev & self::VOWEL) ||
							($flags & self::DIPTHONG) ||
							(rand(0,10) > 3)
					) {
						$should_be = self::CONSONANT;
					} else {
						$should_be = self::VOWEL;
					}
				}
				$prev = $flags;
				$first = 0;
			}
		} while ($feature_flags & (self::PW_ONE_CASE | self::PW_ONE_NUMBER));
		return $result;
	}
}

class pinp_password extends password {
	public static function _generate( $size=8, $pw_flags=3 ) {
		return password::generate( $size, $pw_flags );
	}
}
