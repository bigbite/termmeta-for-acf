<?php

/*
Plugin Name: Termmeta for ACF
Plugin URI: https://github.com/bigbitecreative/termmeta-for-acf
Description: Saves Term Meta fields to termmeta table.
Author: Jon McPartland
Version: 0.1.0
Author URI: https://jon.mcpart.land
Textdomain: termmeta-acf
*/

new class {

	public function __construct() {
		\add_action( 'acf/init', [ $this, 'init' ] );
	}

	public function init() {
		$hasACF = $GLOBALS['acf'] ?? false;
		$verACF = $GLOBALS['acf']->settings['version'] ?? '1.1.1';

		// if we don't have ACF, or it's past v4.5.0, get outta da choppah.
		if ( ! $hasACF || -1 === ( $verACF <=> '4.5.0' ) ) {
			return;
		}

		\add_filter( 'acf/update_value', [ $this, 'save' ], 10, 3 );
		\add_filter( 'acf/load_value',   [ $this, 'load' ], 10, 3 );
	}

	public function save( $value, $identifier, $field ) {
		$ID = $this->get_identifier( $identifier );

		if ( ! $this->should_run( $ID, $field ) ) {
			return $value;
		}

		\update_term_meta( $ID, $field['name'], $value );

		return $value;
	}

	public function load( $value, $identifier, $field ) {
		$ID = $this->get_identifier( $identifier );

		if ( ! $this->should_run( $ID, $field ) ) {
			return $value;
		}

		return \get_term_meta( $ID, $field['name'], true );
	}

	protected function get_identifier( $identifier ) {
		$ID = intval( $identifier, 10 );

		if ( ! $ID ) {
			$tax = $_POST['taxonomy'] ?? $_GET['taxonomy'] ?? '';
			$ID  = str_replace( "{$tax}_", '', $identifier );
			$ID  = intval( $ID, 10 );
		}

		return $ID;
	}

	protected function should_run( $id, $field ) {
		return !! $id && false !== strpos( $field['name'], 'term_metadata' );
	}

};
