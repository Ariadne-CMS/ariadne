<?php
	define("T_G_IDENT",		253);

	class mod_grant {
		function get_token() {
			$this->token = $this->token_ahead;
			$this->token_value = $this->token_ahead_value;
			$this->input = preg_replace('/^([[:space:]]|,)*/', '', $this->input);
			//echo "scanner: input(".$this->input.")<br>\n";
			switch (true) {
				case preg_match('/^([a-zA-Z0-9_][a-zA-Z0-9._:]*)/', $this->input, $regs):
					$this->token_ahead = T_G_IDENT;
					$this->token_ahead_value = $regs[1];
					$this->input = substr($this->input, strlen($regs[1]));
				break;
				case preg_match('/^([]+->=(){}[])/', $this->input, $regs):
					$this->token_ahead = $regs[1];
					$this->token_ahead_value = $regs[1];
					$this->input = substr($this->input, 1);
				break;
				default:
					$this->token_ahead = 0;
					$this->token_ahead_value = '';
			}
			//echo "scanner:: (".$this->token_ahead.") (".$this->token_ahead_value.")<br>\n";
			return $this->token_ahead;
		}

		function parse_stmt(&$grants) {
			$g_array = Array();
			switch ($this->token_ahead) {
				case '-':
					$g_op = '-';
					$this->get_token();
				break;
				case '+':
					$this->get_token();
				default:
					$g_op = '+';
			}

			switch ($this->token_ahead) {
				case '>':
					$g_val = ARGRANTCHILDREN;
					$this->get_token();
				break;
				case '=':
					$g_val = ARGRANTLOCAL;
					$this->get_token();
				break;
				default:
					$g_val = ARGRANTGLOBAL;
			}

			if ($this->token_ahead === '[') {
				$this->get_token();
					while ($this->token_ahead === T_G_IDENT) {
						$this->get_token();
						$g_array[$this->token_value] = $g_val;
					}
				if (!$this->token_ahead === ']') {
					$this->error = 'expected ]';
					return 0;
				}
				$this->get_token();
			} else
			if ($this->token_ahead === T_G_IDENT) {
				$this->get_token();
				$g_array[$this->token_value] = $g_val;
			} else {
				return 0;
			}

			$m_array = Array();

			/* parse modifiers */
			if ($this->token_ahead === '(') {
				$this->get_token();
				do {
					switch ($this->token_ahead) {
						case '>':
							$m_val = ARGRANTCHILDREN;
							$this->get_token();
						break;
						case '=':
							$m_val = ARGRANTLOCAL;
							$this->get_token();
						break;
						default:
							$m_val = $g_val;
					}
					if ($this->token_ahead != T_G_IDENT) {
						$this->error = 'expected modifier near: '.$this->input;
						return 0;
					}
					$this->get_token();
					$m_array[$this->token_value] = $m_val;
				} while ($this->token_ahead && $this->token_ahead != ')');

				if ($this->token_ahead != ')') {
					$this->error = 'modifier list is not closed';
					return 0;
				}
				$this->get_token();

				foreach($g_array as $grant => $g_val) {
					$g_array[$grant] = $m_array;
				}
			}

			foreach($g_array as $grant => $g_val) {
				if ($g_op === '-') {
					unset($grants[$grant]);
				} else {
					$grants[$grant] = $g_val;
				}
			}
			return 1;
		}

		function compile($input, &$grants) {
			$this->input = $input;
			$this->get_token();
			while (!$this->error && $this->parse_stmt($grants));
			ksort($grants);
			foreach( $grants as $key => $value ) {
				if( is_array($value) ) {
					ksort($grants[$key]);
				}
			}
		}
	}
