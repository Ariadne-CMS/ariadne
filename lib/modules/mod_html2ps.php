<?php
	DEFINE("HTML2PS_LOCATION", $this->store->get_config('code')."modules/mod_html2ps/");

	class html2ps {
		function generate($config) {
			global $fpdf_charwidths;
			global $g_baseurl;
			global $g_border;
			global $g_box_uid;
			global $g_boxes;
			global $g_colors;
			global $g_config;
			global $g_css;
			global $g_css_defaults_obj;
			global $g_css_handlers;
			global $g_css_index;
			global $g_css_obj;
			global $g_font_family;
			global $g_font_resolver;
			global $g_font_resolver_pdf;
			global $g_font_size;
			global $g_frame_level;
			global $g_html_entities;
			global $g_image_cache;
			global $g_last_assigned_font_id;
			global $g_list_item_nums;
			global $g_manager_encodings;
			global $g_media;
			global $g_predefined_media;
			global $g_pt_scale;
			global $g_px_scale;
			global $g_stylesheet_title;
			global $g_table_border;
			global $g_tag_attrs;
			global $g_unicode_glyphs;
			global $g_utf8_converters;
			global $g_utf8_to_encodings_mapping_pdf;
			global $g_valign;
			global $psdata;
			global $g_font_style;
			global $g_font_family;
			global $g_font_weight;
			global $g_font_size;
			global $g_line_height;
//			global $base_font_size;

			$context = pobject::getContext();
			$me = $context["arCurrentObject"];

			require_once(HTML2PS_LOCATION.'pipeline.factory.class.php');
			set_time_limit(600);

			check_requirements();

			$g_baseurl = trim($config['URL']);

			if ($g_baseurl === "") {
				$me->error = "Please specify URL to process!";
				return false;
			}

			// Add HTTP protocol if none specified
			if (!preg_match("/^https?:/",$g_baseurl)) {
				$g_baseurl = 'http://'.$g_baseurl;
			}

			$g_css_index = 0;

			// Title of styleshee to use (empty if no preferences are set)
			$g_stylesheet_title = "";

			$g_config = array(
				'cssmedia'			=> isset($config['cssmedia']) ? $config['cssmedia'] : "screen",
				'convert'			 => isset($config['convert']),
				'media'				 => isset($config['media']) ? $config['media'] : "A4",
				'scalepoints'	 => isset($config['scalepoints']),
				'renderimages'	=> isset($config['renderimages']),
				'renderfields'	=> isset($config['renderfields']),
				'renderforms'	 => isset($config['renderforms']),
				'pslevel'			 => isset($config['pslevel']) ? $config['pslevel'] : 2,
				'renderlinks'	 => isset($config['renderlinks']),
				'pagewidth'		 => isset($config['pixels']) ? (int)$config['pixels'] : 800,
				'landscape'		 => isset($config['landscape']),
				'method'				=> isset($config['method']) ? $config['method'] : "fpdf" ,
				'margins'			 => array(
					'left'		=> isset($config['leftmargin'])	 ? (int)$config['leftmargin']	 : 0,
					'right'		=> isset($config['rightmargin'])	? (int)$config['rightmargin']	: 0,
					'top'		=> isset($config['topmargin'])		? (int)$config['topmargin']		: 0,
					'bottom'	=> isset($config['bottommargin']) ? (int)$config['bottommargin'] : 0
					),
				'encoding'			=> isset($config['encoding']) ? $config['encoding'] : "",
				'ps2pdf'				=> isset($config['ps2pdf'])	 ? $config['ps2pdf']	 : 0,
				'compress'			=> isset($config['compress']) ? $config['compress'] : 0,
				'output'				=> isset($config['output']) ? $config['output'] : 0,
				'pdfversion'		=> isset($config['pdfversion']) ? $config['pdfversion'] : "1.2",
				'transparency_workaround' => isset($config['transparency_workaround']),
				'imagequality_workaround' => isset($config['imagequality_workaround']),
				'draw_page_border'				=> isset($config['pageborder']),
				'debugbox'			=> isset($config['debugbox']),
				'watermarkhtml'			=> isset($config['watermarkhtml']),
				'smartpagebreak'			=> isset($config['smartpagebreak']),
				'html2xhtml'		=> !isset($config['html2xhtml']),
				'mode'					=> 'html'
			);

			// ========== Entry point
			parse_config_file(HTML2PS_LOCATION.'./.html2ps.config');

			// validate input data
			if ($g_config['pagewidth'] == 0) {
				$me->error = "Please specify non-zero value for the pixel width!";
				return false;
			};

			// begin processing

			$g_media = Media::predefined($g_config['media']);
			$g_media->set_landscape($g_config['landscape']);
			$g_media->set_margins($g_config['margins']);
			$g_media->set_pixels($g_config['pagewidth']);

			$g_px_scale = mm2pt($g_media->width() - $g_media->margins['left'] - $g_media->margins['right']) / $g_media->pixels;
			if ($g_config['scalepoints']) {
				$g_pt_scale = $g_px_scale * 1.43; // This is a magic number, just don't touch it, or everything will explode!
			} else {
				$g_pt_scale = 1.0;
			};

			// Initialize the coversion pipeline
			$pipeline = new Pipeline();

			// Configure the fetchers
			$pipeline->fetchers[] = new FetcherURL();

			// Configure the data filters
			$pipeline->data_filters[] = new DataFilterDoctype();
			$pipeline->data_filters[] = new DataFilterUTF8($g_config['encoding']);
			if ($g_config['html2xhtml']) {
				$pipeline->data_filters[] = new DataFilterHTML2XHTML();
			} else {
				$pipeline->data_filters[] = new DataFilterXHTML2XHTML();
			};

			$pipeline->parser = new ParserXHTML();

			$pipeline->pre_tree_filters = array();
			if ($g_config['renderfields']) {
				$pipeline->pre_tree_filters[] = new PreTreeFilterHTML2PSFields("","","");
			};

			if ($g_config['method'] === 'ps') {
				$pipeline->layout_engine = new LayoutEnginePS();
			} else {
				$pipeline->layout_engine = new LayoutEngineDefault();
			};

			$pipeline->post_tree_filters = array();

			// Configure the output format
			if ($g_config['pslevel'] == 3) {
				$image_encoder = new PSL3ImageEncoderStream();
			} else {
				$image_encoder = new PSL2ImageEncoderStream();
			};

			switch ($g_config['method']) {
			 case 'ps':
				 $pipeline->output_driver = new OutputDriverPS($g_config['scalepoints'],
						 $g_config['transparency_workaround'],
						 $g_config['imagequality_workaround'],
						 $image_encoder);
				 break;
			 case 'fastps':
				 $pipeline->output_driver = new OutputDriverFastPS($image_encoder);
				 break;
			 case 'pdflib':
				 $pipeline->output_driver = new OutputDriverPDFLIB($g_config['pdfversion']);
				 break;
			 case 'fpdf':
				 $pipeline->output_driver = new OutputDriverFPDF();
				 break;
			 default:
				$me->error = "Unknown output method";
				return false;
			};

			if ($g_config['debugbox']) {
				$pipeline->output_driver->set_debug_boxes(true);
			}

			if ($g_config['draw_page_border']) {
				$pipeline->output_driver->set_show_page_border(true);
			}

			if ($g_config['ps2pdf']) {
				$pipeline->output_filters[] = new OutputFilterPS2PDF($g_config['pdfversion']);
			}

			if ($g_config['compress']) {
				$pipeline->output_filters[] = new OutputFilterGZip();
			}

			$pipeline->destination = new DestinationBrowser($g_baseurl);
			$dest_filename = $pipeline->destination->filename_escape($pipeline->destination->get_filename());
			ldSetContent("application/pdf");
			if (!$me->cached("html2ps_".md5($dest_filename))) {
				// Start the conversion

				$status = $pipeline->process($g_baseurl, $g_media);
				if ($status == null) {
					ldSetContent("text/html");
					$me->error = "Error in conversion pipeline: ".$pipeline->error_message();
					$me->savecache(0);
					return false;
				} else {
					$me->savecache(999);
				}
			}
			return true;
		}
	}

	class pinp_html2ps extends html2ps {
		function _generate($config) {
			return html2ps::generate($config);
		}
	}
