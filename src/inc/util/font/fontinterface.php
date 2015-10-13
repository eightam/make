<?php
/**
 * @package Make
 */


interface MAKE_Util_Font_FontInterface extends MAKE_Util_LoadInterface {
	public function add_font_module( $module_id, MAKE_Util_Font_Module_FontModuleInterface $module );

	public function remove_font_module( $module_id );

	public function has_font_module( $module_id );

	public function get_font_module( $module_id );

	public function get_font_module_label( $module_id );

	public function get_font_choices( $module_id = null, $headings = true );
}