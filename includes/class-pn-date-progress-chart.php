<?php

class pn_date_progress_chart {

	var $month_year = 'n/d';
	var $font = 'arial.ttf';
	var $font_size = 9;
	var $bottom_line_y = 26;
	var $tick_height = 3;
	var $text_top_padding = 3;
	var $bar_max_width;
	var $bar_width;
	var $bar_height = 22;
	var $bar_top_x = 2;
	var $bar_start = 18;
	var $bar_end = 244;
	var $im;
	var $textcolor;
	var $bluebar;
	var $greyline;
	var $width = 260;
	var $height = 45;

	function init() {

		$this->font = plugin_dir_path( __FILE__ ) . $this->font;

		$this->bar_max_width = $this->bar_end - $this->bar_start;

		// create image resource
		$this->im = imagecreate($this->width, $this->height);

		//background
		$bg = imagecolorallocate($this->im, 246, 246, 247);

		// grey text
		$this->textcolor = imagecolorallocate($this->im, 103, 103, 103);

		// blue progress bar
		$this->bluebar = imagecolorallocate($this->im, 26, 117, 187);

		// lines and ticks
		$this->greyline = imagecolorallocate($this->im, 175, 175, 175);

	}


	function draw_date($date, $text_center) {
		$date_string = date($this->month_year, date_timestamp_get( $date ) );
		$box = imagettfbbox($this->font_size, 0, $this->font, $date_string);
		$text_width = abs($box[4] - $box[0]);
		$text_height = abs($box[5] - $box[1]);
		$text_start = $text_center - ($text_width / 2);
		imagettftext($this->im, $this->font_size, 0, $text_start, $this->bottom_line_y + $text_height + $this->tick_height + $this->text_top_padding, $this->textcolor, $this->font, $date_string);
	}

	function draw_line($from_x, $from_y, $to_x, $to_y) {
		imageline($this->im, $from_x, $from_y, $to_x, $to_y, $this->greyline);
	}

	function draw_progress_bar($percent_progress) {
		$bar_width = ceil($this->bar_max_width * $percent_progress);
		imagefilledrectangle($this->im, $this->bar_start, $this->bar_top_x, $this->bar_start +	 $bar_width, $this->bar_top_x + $this->bar_height, $this->bluebar);
	}


}