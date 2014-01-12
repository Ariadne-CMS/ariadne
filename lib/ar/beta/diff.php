<?php
	global $AR;
	require_once($AR->dir->install."/lib/includes/diff/DiffEngine.php");
	require_once($AR->dir->install."/lib/includes/diff/ariadne.diff.inc");
	
	ar_pinp::allow( 'ar_beta_diff' );

	class ar_beta_diff extends arBase {
		public static function diff ($a, $b){
			$a = is_array($a) ? $a : explode("\n", $a);
			$b = is_array($b) ? $b : explode("\n", $b);

			$diff = new Diff($a, $b);

			if ($diff) {
				$formatter = new AriadneDiffFormatter();
				$result = $formatter->format($diff);

				$rows = ar_html::nodes();
				foreach ($result as $line) {
					$cells = ar_html::nodes();
					foreach ($line as $content) {
						if (is_array($content)) {
							$options = array();
							if ($content['class']) {
								$options['class'] = $content['class'];
							}
							if ($content['colspan']) {
								$options['colspan'] = $content['colspan'];
							}

							$cell = ar_html::tag("td", $options, $content['data']);
							$cells[] = $cell;
						} else {
							$cells[] = ar_html::tag("td", $content);
						}
					}
					$rows[] = ar_html::tag("tr", $cells);
				}

				if (sizeof($rows)) {
					$table = ar_html::tag("table", array("class" => "diff"), $rows);
					$table->insertBefore(ar_html::tag("col", array("class" => "content")), $table->firstChild);
					$table->insertBefore(ar_html::tag("col"), $table->firstChild);
					$table->insertBefore(ar_html::tag("col", array("class" => "content")), $table->firstChild);
					$table->insertBefore(ar_html::tag("col"), $table->firstChild);

					return $table;
				}
			}
		}

		public static function style () {
			$css = ar_css::stylesheet()
			->import("
				html.js .diff-js-hidden { display:none; }
				.diff-inline-metadata {
				  padding:4px;
				  border:1px solid #ddd;
				  background:#fff;
				  margin:0px 0px 10px;
				  }

				.diff-inline-legend { font-size:11px; }

				.diff-inline-legend span,
				.diff-inline-legend label { margin-right:5px; }

				/**
				 * Inline diff markup
				 */
				span.diff-deleted { color:#ccc; }
				span.diff-deleted img { border: solid 2px #ccc; }
				span.diff-changed { background:#ffb; }
				span.diff-changed img { border:solid 2px #ffb; }
				span.diff-added { background:#cfc; }
				span.diff-added img { border: solid 2px #cfc; }

				/**
				 * Traditional split diff theming
				 */
				table.diff {
				  border-spacing: 0px;
				  border-collapse: collapse;
				  margin-bottom: 20px;
				  width: 100%;
				  table-layout: fixed;
				}
				table.diff col {
					width: 1.4em;
				}
				table.diff col.content {
					width: 50%;
				}
				table.diff tr.even, table.diff tr.odd {
				  background-color: inherit;
				  border: none;
				}
				td.diff-prevlink {
				  text-align: left;
				}
				td.diff-nextlink {
				  text-align: right;
				}
				td.diff-section-title, div.diff-section-title {
				  background-color: #f0f0ff;
				  font-size: 0.83em;
				  font-weight: bold;
				  padding: 0.1em 1em;
				}
				table.diff td {
					vertical-align: top;
					border-bottom: 1px solid #CCCCCC;
				}

				td.diff-deletedline {
				  background-color: #ffa;
				  width: 50%;
				}
				td.diff-addedline {
				  background-color: #afa;
				  width: 50%;
				}
				td.diff-context {
				  background-color: #fafafa;
				}
				span.diffchange {
				  color: #f00;
				  font-weight: bold;
				}

				table.diff col.diff-marker {
				  width: 1.4em;
				}
				table.diff col.diff-content {
				  width: 50%;
				}
				table.diff th {
				  padding-right: inherit;
				}
				table.diff td div {
				  overflow: auto;
				  padding: 0.1ex 0.5em;
				  word-wrap: break-word;
				}
				table.diff td {
				  padding: 0.1ex 0.4em;
				}
			");

			return $css;
		}
	}
?>