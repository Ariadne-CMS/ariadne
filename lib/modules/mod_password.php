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

		global $elements, $NUM_ELEMENTS, $VOWEL, $DIPTHONG, $CONSONANT, $PW_ONE_CASE, $NOT_FIRST, $PW_ONE_NUMBER;


/*
 * Flags for the pw_element
 */
	$CONSONANT	=0x00001;
	$VOWEL		=0x00002;
	$DIPTHONG	=0x00004;
	$NOT_FIRST	=0x00008;

/*
 * Flags for the pwgen function
 */
	$PW_ONE_NUMBER	=0x00001;
	$PW_ONE_CASE	=0x00002;

/*
 * Password elements
 */

	$elements = array(
	array( "a",	$VOWEL ),
	array( "ae", $VOWEL | $DIPTHONG ),
	array( "ah",	$VOWEL | $DIPTHONG ),
	array( "ai", $VOWEL | $DIPTHONG ),
	array( "b",  $CONSONANT ),
	array( "c",	$CONSONANT ),
	array( "ch", $CONSONANT | $DIPTHONG ),
	array( "d",	$CONSONANT ),
	array( "e",	$VOWEL ),
	array( "ee", $VOWEL | $DIPTHONG ),
	array( "ei",	$VOWEL | $DIPTHONG ),
	array( "f",	$CONSONANT ),
	array( "g",	$CONSONANT ),
	array( "gh", $CONSONANT | $DIPTHONG | $NOT_FIRST ),
	array( "h",	$CONSONANT ),
	array( "i",	$VOWEL ),
	array( "ie", $VOWEL | $DIPTHONG ),
	array( "j",	$CONSONANT ),
	array( "k",	$CONSONANT ),
	array( "l",	$CONSONANT ),
	array( "m",	$CONSONANT ),
	array( "n",	$CONSONANT ),
	array( "ng",	$CONSONANT | $DIPTHONG | $NOT_FIRST ),
	array( "o",	$VOWEL ),
	array( "oh",	$VOWEL | $DIPTHONG ),
	array( "oo",	$VOWEL | $DIPTHONG),
	array( "p",	$CONSONANT ),
	array( "ph",	$CONSONANT | $DIPTHONG ),
	array( "qu",	$CONSONANT | $DIPTHONG),
	array( "r",	$CONSONANT ),
	array( "s",	$CONSONANT ),
	array( "sh",	$CONSONANT | $DIPTHONG),
	array( "t",	$CONSONANT ),
	array( "th",	$CONSONANT | $DIPTHONG),
	array( "u",	$VOWEL ),
	array( "v",	$CONSONANT ),
	array( "w",	$CONSONANT ),
	array( "x",	$CONSONANT ),
	array( "y",	$CONSONANT ),
	array( "z",	$CONSONANT )
);

	$NUM_ELEMENTS = count( $elements );

class password {

	public static function generate( $size=8, $pw_flags=3 ) {
		global $elements, $NUM_ELEMENTS, $VOWEL, $DIPTHONG, $CONSONANT, $PW_ONE_CASE, $NOT_FIRST, $PW_ONE_NUMBER;


		do {
			$c = 1;
			$prev = 0;
			$should_be = 0;
			$first = 1;
			$result = "";

			$feature_flags = $pw_flags;

			$should_be = (rand(0,1) ? $VOWEL : $CONSONANT);

			while( $c < $size ) {
				$i = rand( 0, ($NUM_ELEMENTS - 1 ) );
				$str = $elements[$i][0];
				$len = strlen($str);
				$flags = $elements[$i][1];

				/* Filter on the basic type of the next element */
				if (($flags & $should_be) == 0)
					continue;
				/* Handle the $NOT_FIRST flag */
				if ($first && ($flags & $NOT_FIRST))
					continue;
				/* Don't allow $VOWEL followed a Vowel/Dipthong pair */
				if ((prev & $VOWEL) && ($flags & $VOWEL) &&
				    ($flags & $DIPTHONG))
					continue;
				/* Don't allow us to overflow the buffer */
				if ($len > ( $size - $c ) )
					continue;
				/*
				 * OK, we found an element which matches our criteria,
				 * let's do it!
				 */

				$result = $result.$str;

				/* Handle $PW_ONE_CASE */
				if ($feature_flags & $PW_ONE_CASE) {
					if (($first || $flags & $CONSONANT) &&
					    (rand(0,10) < 3)) {
						$result = substr_replace( $result, strtoupper( substr( $result, $c, 1 ) ), $c);
						$feature_flags &= ~$PW_ONE_CASE;
					}
				}

				$c += $len;

				/* Time to stop? */
				if ($c >= $size)
					break;

				/*
				 * Handle PW_ONE_NUMBER
				 */
				if ($feature_flags & $PW_ONE_NUMBER) {
					if (!$first && (rand(0,10) < 3)) {
						$result = substr_replace( $result, rand(0,9), $c);
						$feature_flags &= ~PW_ONE_NUMBER;
						$first = 1;
						$prev = 0;
						$should_be = rand(0,1) ?
							$VOWEL : $CONSONANT;
						continue;
					}
				}

				/*
				 * OK, figure out what the next element should be
				 */
				if ($should_be == $CONSONANT) {
					$should_be = $VOWEL;
				} else { /* $should_be == $VOWEL */
					if (($prev & $VOWEL) ||
					    ($flags & $DIPTHONG) ||
					    (rand(0,10) > 3))
						$should_be = $CONSONANT;
					else
						$should_be = $VOWEL;
				}
				$prev = $flags;
				$first = 0;
			}
		} while ($feature_flags & ($PW_ONE_CASE | $PW_ONE_NUMBER));
			return $result;
	}
}

class pinp_password extends password {
	public static function _generate( $size=8, $pw_flags=3 ) {
		return password::generate( $size, $pw_flags );
	}
}
