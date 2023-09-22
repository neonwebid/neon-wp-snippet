<?php

if ( class_exists('NeonSEOShortcode') ) {
	class NeonSEOShortcode {
		
		public function __construct() {
	
	   		# use [neon_current_year]
			add_shortcode('neon_current_year', [$this, 'currentYear']);
	
	    		# use [neon_month_year]
			add_shortcode('neon_month_year', [$this, 'currentMonthYear']);
	
	    		# use [neon_month_year_id]
			add_shortcode('neon_month_year_id', [$this, 'currentMonthYearId']);
	
			# enable shortcode in yoast
			add_filter('wpseo_title', [$this, 'shortcodeAllowed']);
			add_filter('wpseo_metadesc', [$this, 'shortcodeAllowed']);
	
			# enable shortcode in AIOSeo
			add_filter('aioseo_title', [$this, 'shortcodeAllowed']);
			add_filter('aioseo_description', [$this, 'shortcodeAllowed']);
	
			# enable shortcode in RankMath
			add_filter('rank_math/frontend/title', [$this, 'shortcodeAllowed']);
			add_filter('rank_math/frontend/description', [$this, 'shortcodeAllowed']);
	
			# enable shortcode in post title bar
			add_filter('wp_title', [$this, 'shortcodeAllowed']);
	
		}
	
		public function shortcodeAllowed($args) {
			return do_shortcode($args);
		}
	
		public function currentYear() {
			return date('Y');
		}
	
		public function currentMonthYear() {
			return date('F Y');
		}
	
		public function currentMonthYearId() {
			return $this->translationMonthEnToId( date('n') ) . ' ' . date('Y');
		}
	
		private function translationMonthEnToId($numeric_month) {
			$monthInIndonesian = array(
		        1 => 'Januari',
		        2 => 'Februari',
		        3 => 'Maret',
		        4 => 'April',
		        5 => 'Mei',
		        6 => 'Juni',
		        7 => 'Juli',
		        8 => 'Agustus',
		        9 => 'September',
		        10 => 'Oktober',
		        11 => 'November',
		        12 => 'Desember'
		    );
	
		    return ! empty($monthInIndonesian[$numeric_month]) ? $monthInIndonesian[$numeric_month] : $numeric_month;
		}
	}
	
	new NeonSEOShortcode();

}

